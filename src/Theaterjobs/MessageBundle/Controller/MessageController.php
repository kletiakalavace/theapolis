<?php

namespace Theaterjobs\MessageBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use FOS\ElasticaBundle\Paginator\TransformedPaginatorAdapter;
use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Acl\Exception\Exception;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\MainBundle\Transformer\ElasticaToRawTransformer;
use Theaterjobs\MessageBundle\Entity\Message;
use Theaterjobs\MessageBundle\Entity\MessageMetadata;
use Theaterjobs\MessageBundle\Entity\Thread;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Theaterjobs\MessageBundle\Utils\NodeEmitter;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\User;


/**
 * Class MessageController
 * 
 * @package Theaterjobs\MessageBundle\Controller
 * 
 * @author Jurgen Rexhmati <rexhmatijurgen@gmail.com>
 * @Security("has_role('ROLE_USER')")
 */
class MessageController extends BaseController
{
    /**
     * Loads all messages
     * 
     * @Route("/", name="tj_message_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('TheaterjobsMessageBundle:Message:index.html.twig');
    }

    /**
     * Replies a single thread and emits to online users through node
     *
     * @Route("/thread/{id}/reply", name="tj_message_thread_reply",  options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request
     * @param Thread  $thread
     *
     * @return RedirectResponse | JsonResponse
     */
    public function replyMessageAction(Request $request, Thread $thread)
    {
        if (!$this->_hasPermissions($thread, $this->getUser())) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->getTranslator()->trans('messages.notFound.Thread')
            ]);
        }

        $body = $request->request->get('body');
        if (!$this->_validMessage($body)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->getTranslator()->trans(
                    'messages.error.invalid.format'
                )
            ]);
        }

        $composer = $this->container->get('fos_message.composer');
        $sender = $this->getUser();

        $message = $composer->reply($thread)
            ->setSender($sender)
            ->setBody($body)
            ->getMessage();

        $sender = $this->container->get('fos_message.sender');
        $sender->send($message);

        $thread->setIsReadByParticipant($this->getUser(), true);
        $this->getEm()->persist($thread);
        $this->getEm()->flush();

        $templateSender = $this->render(
            'TheaterjobsMessageBundle:Message:singleMessage.html.twig',
            array (
                'message'   => $message,
                'sender'    => true
            )
        )->getContent();
        $templateReceiver = $this->render(
            'TheaterjobsMessageBundle:Message:singleMessage.html.twig',
            array (
                'message'   => $message,
                'sender'    => false
            )
        )->getContent();

        //encode thread id
        $socketUserID = $request->request->get('socketUserID');
        if ($socketUserID) {
            $socketThreadID = $this->_getSocketThreadID($thread);
            $templates = ['sender' => $templateSender, 'receiver' => $templateReceiver];
            $sent = $this->get('node_emiter')->emitMessage($templates, $socketUserID, $socketThreadID, $this->getUser());
        } else {
            $sent = false;
        }

        return new JsonResponse(['success' => true, 'message' => $sent ? false : $templateSender]);
    }

    /**
     * Renders view
     *
     * @Route("/compose/{slug}", name="tj_message_thread_create")
     * @Method("GET")
     *
     * @param Profile $profile
     * @return Response
     */
    public function createAction(Profile $profile)
    {
        return $this->render('TheaterjobsMessageBundle:Message/New:new.html.twig',
            ['profile' => $profile]);
    }

    /**
     * Create a new thread between two users
     * 
     * @Route("/compose", name="tj_message_thread_new", options={"expose" = true})
     * @Method("POST")
     * 
     * @param Request $request
     * 
     * @return RedirectResponse | JsonResponse
     */
    public function newAction(Request $request)
    {
        $composer = $this->container->get('fos_message.composer');
        $userId =  $request->request->get('user');

        $recipient = $this->getEm()->getRepository(User::class)->findOneBy(array('id' => $userId));
        $isAdmin = $this->getUser()->hasRole('ROLE_ADMIN');
        $talkingToSelf = ($recipient == $this->getUser());

        //User not found
        //||
        //User found but profile !published & current user !admin
        //Sending message to his self
        if (
            !$recipient
            ||
            (!$recipient->getProfile()->getIsPublished() && !$isAdmin)
            ||
            $talkingToSelf
        ) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->getTranslator()->trans(
                    'messages.error.parcticipant.notFound'
                )
            ]);
        }
        //Validate message and subject
        $body = $request->request->get('body');
        $subject = $request->request->get('subject');
        if (!$this->_validMessage($body) || !$this->_validMessage($subject)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->getTranslator()->trans(
                    'messages.error.invalid.format'
                )
            ]);
        }
        //Create Thread
        $thread = $composer->newThread()
            ->setSender($this->getUser())
            ->setSubject($subject)
            ->setBody($body);
        $thread->addRecipient($recipient);
        //Send Message
        $message = $thread->getMessage();
        $this->container->get('fos_message.sender')->send($message);
        //Set as read and persist
        $message->setIsReadByParticipant($this->getUser(), true);
        $this->getEm()->persist($message);
        $this->getEm()->flush();

        $route = $this->generateUrl('tj_message_index');
        return new JsonResponse(['success' => true, 'route' => $route]);
    }

    /**
     * Loads thread messages
     *
     * @Route("/thread/{id}", name="tj_message_show_single",
     *     condition="request.isXmlHttpRequest()", options={"expose"=true})
     *
     * @param Thread $thread
     * 
     * @return Response
     */
    public function singleThreadAction(Thread $thread)
    {
        if (!$this->_hasPermissions($thread, $this->getUser())) {
            $result = [
                'success' => false,
                'message' => $this->getTranslator()->trans('messages.notFound.Thread')
            ];
            return new JsonResponse($result);
        }

        $limit = $this->getParameter('paginationLimit');

        $socketThreadID = $this->_getSocketThreadID($thread);
        $receiver = $this->_getReceiver($thread, $this->getUser());
        $query = $this->getEM()->getRepository(Message::class)->threadMessages($thread, $this->getUser());
        $thread->setIsReadByParticipant($this->getUser(), true);
        $this->getEM()->flush();

        $paginator  = $this->getPaginator();
        $messages = $paginator->paginate($query, 1, $limit);

        $options = array(
            'receiver' => $receiver,
            "thread" => $thread,
            "messages" => $messages,
            'socketThreadID' => $socketThreadID,
            'senderID' => md5($this->getUser()->getId())
        );

        $template = $this->renderView('TheaterjobsMessageBundle:Message:singleThread.html.twig', $options);
        return new JsonResponse(['success' => true,'result' => $template]);
    }

    /**
     * Makes thread seen by the user
     *
     * @Route("/seen/{id}", name="tj_message_seen", options={"expose"=true})
     *
     * @param Thread $thread
     *
     * @return JsonResponse
     */
    public function seenAction(Thread $thread)
    {
        if (!$this->isPartOfThread($thread, $this->getUser())) {
            $result = [
                'success' => false,
                'message' => $this->getTranslator()->trans('messages.notFound.Thread')
            ];
            return new JsonResponse($result);
        }
        $thread->setIsReadByParticipant($this->getUser(), true);
        $this->getEm()->persist($thread);
        $this->getEm()->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Returns messages with pagination
     *
     * @Route("/last/{id}", name="tj_message_show_more", options={"expose"=true})
     * @Method("POST")
     *
     * @param $thread
     * @param $request
     *
     * @return JsonResponse
     */
    public function lastMessagesAction(Thread $thread, Request $request)
    {
        if (!$this->isPartOfThread($thread, $this->getUser())) {
            $result = [
                'success' => false,
                'message' => $this->getTranslator()->trans('messages.notFound.Thread')
            ];
            return new JsonResponse($result);
        }

        //Parameter for pagination limit
        $limit = $this->getParameter('paginationLimit');

        //Last message id
        $lastMsgID = $request->request->getInt('after');
        $lastMsgID = (!empty($lastMsgID)) ? $lastMsgID : -1;

        //Query to retrieve messages
        $query = $this->getEM()->getRepository(Message::class)
            ->paginateMessages($thread, $this->getUser(), $lastMsgID);

        //Get service
        $paginator  = $this->getPaginator();

        $messages = $paginator->paginate($query, 1, $limit);

        //Get all messages
        $template = $this->renderView('TheaterjobsMessageBundle:Message/Pagination/messages.html.twig',
            ['messages' => $messages]
        );

        $result = [
            'success' => true,
            'messages' => $template
        ];

        return new JsonResponse($result);
    }

    /**
     * @Route("/search", name="tj_message_search", options={"expose"=true})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        $page = $request->request->get('page');
        $query = $request->request->get('q');
        $limit = $this->getParameter('paginationThreadLimit');

        $esm = $this->get('fos_elastica.manager');
        $query = $esm->getRepository(Thread::class)->searchThreadsByParticipant($query, $this->getUser()->getId());
        $result = $this->get('fos_elastica.index.theaterjobs.thread');

        $pagination = $this->getPaginator()->paginate(
            new TransformedPaginatorAdapter(
                $result,
                $query,
                [],
                new ElasticaToRawTransformer()
            ),
            $page ? $page : 1, $limit
        );
        $threads = [];
        //Get ids from elastica
        foreach ($pagination->getItems() as $item) {
            $threads[] = $item->getData()['id'];
        }
        //Find entities in db
        $threads = $this->getEM()->getRepository(Thread::class)->searchSorted($threads);

        $template = $this->render('TheaterjobsMessageBundle:Message\Pagination\threads.html.twig',[
                'threads' => $threads
            ])->getContent();

        return new JsonResponse([
            'success' => true,
            'result' => $template
        ]);
    }

    /**
     * @Route("/load/newThread/{slug}", name="tj_messages_load_new_thread")
     *
     * @param Profile $profile
     * @return JsonResponse
     */
    public function loadNewThreadAction(Profile $profile)
    {
        $template = $this->render('TheaterjobsMessageBundle:Message\New\loadNew.html.twig',[
                'profile' => $profile
            ]
        )->getContent();

        return new JsonResponse(['success' => true,'result' => $template]);
    }

    /**
     * Deletes a single message
     *
     * @Route("/message/{id}/delete", name="tj_messages_delete_message")
     * @Method("POST")
     *
     * @param Request $request
     * @param Message $message
     * @return JsonResponse
     */
    public function deleteMessageAction(Request $request, Message $message)
    {
        $thread = $message->getThread();
        $hasPerm = $this->_hasPermissions($thread, $this->getUser());
        $isMsgOwn = $this->_isMessageOwner($message, $this->getUser());
        if (!$hasPerm || !$isMsgOwn) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->getTranslator()->trans('messages.notFound.Thread')
            ]);
        }

        $message->setDeletedBy($this->getUser());
        $this->getEM()->persist($message);
        $this->getEM()->flush();


        //encode thread id
        $socketUserID = $request->get('socketUserID');
        $txt = $this->getTranslator()->trans('messages.deletedBy %user%',
            ['%user%' => $this->getProfile()->defaultName()],
            'messages'
        );
        if ($socketUserID) {
            $socketThreadID = $this->_getSocketThreadID($thread);
            $template = [ 'message' => $txt, 'messageID' => $message->getId() ];
            $sent = $this->get('node_emiter')
                ->emitDelete($template, $socketUserID, $socketThreadID, $this->getUser());

        } else {
            $sent = false;
            $template = $txt;
        }

        return new JsonResponse(['success' => true, 'result' => $sent ? false : $template]);
    }


    /**
     * Deletes a single message
     *
     * @Route("/loadThread/{id}", name="tj_message_load_thread", options={"expose"=true})
     * @Method("GET")
     *
     * @param Request $request
     * @param Thread $thread
     * @return JsonResponse
     */
    public function loadThreadAction(Request $request, Thread $thread)
    {
        if (!$this->_hasPermissions($thread, $this->getUser())) {
            $result = [
                'success' => false,
                'message' => $this->getTranslator()->trans('messages.notFound.Thread')
            ];
            return new JsonResponse($result);
        }

        $sideThread = $this->render('TheaterjobsMessageBundle:Message/Partial:sideThread.html.twig', ['thread' => $thread])->getContent();
        $result = [
            'success' => true,
            'result' => $sideThread
        ];
        return new JsonResponse($result);
    }

    /**
     * Check if a user is part of a thread
     *
     * @param Thread $thread
     * @param User   $user
     *
     * @return boolean
     */
    protected function isPartOfThread(Thread $thread, User $user)
    {
        $participants = $thread->getParticipants();
        return in_array($user, $participants);
    }

    /**
     * Check if a user has permissions to a particular thread
     *
     * @param Thread $thread
     * @param User   $user
     *
     * @return boolean
     */
    private function _hasPermissions($thread, $user)
    {
        $isPartOf   = $this->isPartOfThread($thread, $user);
        $isDeleted  = $thread->isDeletedByParticipant($user);
        return !(!$isPartOf || $isDeleted);
    }

    /**
     * Check if a user is owner of a message
     *
     * @param Message $message
     * @param User   $user
     *
     * @return boolean
     */
    private function _isMessageOwner($message, $user)
    {
        return $message->getSender() === $user;
    }

    /**
     * Encodes thread id to join a unique room
     *
     * @param Thread $thread
     *
     * @return string
     */
    private function _getSocketThreadID(Thread $thread)
    {
        //encode thread id and generate a unique one
        return md5(
            json_encode(
                [
                    "threadID"  => $thread->getId(),
                    'uniqueVal' => md5($thread->getCreatedBy())
                ]
            )
        );
    }

    /**
     * Validates a message
     *
     * @param string $message
     *
     * @return boolean
     */
    private function _validMessage($message)
    {
        //Maximum length of message
        $MAXLENGTH = $this->getParameter('messageLength');

        $isEmpty = empty(trim($message));
        $isBig = (strlen(trim($message)) > $MAXLENGTH) ? true : false;

        if ($isEmpty || $isBig) {
            return false;
        }

        return true;
    }

    /**
     * Returns the receiver of the conversation
     *
     * @param $thread
     * @param $user
     *
     * @return boolean
     */
    private function _getReceiver($thread, $user) {
        $participants = $thread->getParticipants();
        if(count($participants) == 2){
            if($thread->getCreatedBy() == $user) {
                $receiver = $participants[1];
            }else {
                $receiver = $participants[0];
            }
        } else {
            $receiver = $participants[0];
        }

        return $receiver;
    }
}
