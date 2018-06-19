<?php

namespace Theaterjobs\MembershipBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Theaterjobs\MembershipBundle\Entity\DebitAccount;
use JMS\DiExtraBundle\Annotation as DI;
use Theaterjobs\MainBundle\Controller\BaseController;
use Theaterjobs\MembershipBundle\Entity\SepaMandate;
use Theaterjobs\MembershipBundle\Service\Sepa;
use Theaterjobs\ProfileBundle\Entity\Profile;
use Theaterjobs\UserBundle\Entity\Notification;
use Theaterjobs\UserBundle\Entity\User;
use Theaterjobs\UserBundle\Event\NotificationEvent;

/**
 * DebitAccountController
 *
 * @category DebitAccountController
 * @package  Theaterjobs\MembershipBundle\Controller
 * @author   Heiko Jurgeleit <jurgeleit.heiko@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @Route("/debit-account")
 */
class DebitAccountController extends BaseController
{

    /**
     * @DI\Inject("doctrine.orm.entity_manager")
     * @var ObjectManager $em
     */
    private $em;

    /**
     * @DI\Inject("theaterjobs_membership.sepa")
     * @var Sepa $sepa
     */
    private $sepa;

    /**
     * @DI\Inject("knp_snappy.pdf")
     * @var \Knp\Snappy\GeneratorInterface
     */
    private $pdfGenerator;

    /**
     * Edit debitAccount
     *
     * @param DebitAccount $debitAccount
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/edit/{id}", name="tj_membership_debitaccount_edit", requirements={"id" = "\d+"})
     */
    public function editAction(DebitAccount $debitAccount)
    {
        $form = $this->createEditForm('theaterjobs_membership_debit_account_type', $debitAccount, [], 'tj_membership_debit_account_update', ['id' => $debitAccount->getId()]);
        $bankName = $this->sepa->generateBic($debitAccount->getIban());

        return $this->render('TheaterjobsMembershipBundle:DebitAccount:edit.html.twig',[
            'form' => $form->createView(),
            'bankName' => $bankName['bankName']
        ]);
    }

    /**
     * Update DebitAccout
     *
     * @param Request $request
     * @param DebitAccount $debitAccount
     * @return JsonResponse
     * @Route("/update/{id}", name="tj_membership_debit_account_update", defaults={"id" = null})
     * @Method("PUT")
     */
    public function updateAction(Request $request, DebitAccount $debitAccount)
    {
        $profile = $debitAccount->getProfile();
        $isOwner = !$this->isAnon() && $this->getUser()->isEqual($profile->getUser());
        $user = $profile->getUser();
        $oldDebit = clone $debitAccount;

        $editForm = $this->createEditForm('theaterjobs_membership_debit_account_type', $debitAccount, [], 'tj_membership_debit_account_update', ['id' => $debitAccount->getId()]);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            if (!$oldDebit->isEqual($debitAccount)) {
                $user->setBankConfirmed(true);

                $this->generateSepaMandate($request, $profile, $debitAccount);
                $this->sendNotification($user, $debitAccount);
                $this->get('app.mailer.twig_swift')->updateBankDataEmail($profile);
                $this->em->flush();
            }
            $bankName = $this->sepa->generateBic($debitAccount->getIban());
            $lastSepa = $profile->getLastSepaMandate();
            $content = $this->render('TheaterjobsUserBundle:Partial:bankingData.html.twig',
                [
                    'debitAccount' => $debitAccount,
                    'bankName' =>  $bankName,
                    'lastSepa' => $lastSepa,
                    'owner' => $isOwner
                ]
            );
            return new JsonResponse ([
                'success' => true,
                'message' => $this->getTranslator()->trans("flash.success.change.debit.account"),
                'data' => $content->getContent()
            ]);
        }
        return new JsonResponse([
            'success' => false,
            'errors' => $this->getErrorMessagesAJAX($editForm)
        ]);
    }

    /**
     * @Route("/new", name="tj_membership_debitaccount_new")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $form = $this->createCreateForm(
            'theaterjobs_membership_debit_account_type',
            new DebitAccount(),
            [],
            'tj_membership_debitaccount_create'
        );
        return $this->render('TheaterjobsMembershipBundle:DebitAccount:new.html.twig', ['form' => $form->createView()]);
    }


    /**
     * Creates a new PersonalData entity.
     *
     * @Route("/create", name="tj_membership_debitaccount_create")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $profile = $this->getProfile();
        $debitAccount = new DebitAccount();
        $debitAccount->setProfile($profile);
        $form = $this->createCreateForm(
            'theaterjobs_membership_debit_account_type',
            $debitAccount, [],
            'tj_membership_debitaccount_create'
        );
        $form->handleRequest($request);
        // TODO Return this error straight away and not to iban, add possibility to render non form errors on fe
        if ($profile->getDebitAccount()) {
            $err = $this->getTranslator()->trans('debit.data.limit.is.one');
            $form->get('iban')->addError( new FormError($err));
        }
        if ($form->isValid()) {
            $this->em->persist($debitAccount);
            $this->em->flush();
            $bankName = $this->sepa->generateBic($debitAccount->getIban());
            $content = $this->render('TheaterjobsUserBundle:Partial:bankingData.html.twig',[
                'debitAccount' => $debitAccount,
                'bankName' =>  $bankName,
                'lastSepa' => $profile->getLastSepaMandate(),
                'owner' => true
            ]);
            return new JsonResponse ([
                'success' => true,
                'data' => $content->getContent()
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'errors' => $this->getErrorMessagesAJAX($form)
        ]);
    }

    /**
     * Send notification to user about changing bank data
     * @param User $user
     * @param DebitAccount $debitAccount
     */
    private function sendNotification(User $user, DebitAccount $debitAccount)
    {
        $notification = new Notification();
        $title = 'tj.notification.bank.data.changed';
        $notification
            ->setTitle($title)
            ->setCreatedAt(new \DateTime())
            ->setDescription('')
            ->setRequireAction(false)
            ->setLink('tj_user_account_settings')
            ->setLinkKeys(['tab' => 'billing']);

        $notificationEvent = (new NotificationEvent())
            ->setObjectClass(DebitAccount::class)
            ->setObjectId($debitAccount->getId())
            ->setNotification($notification)
            ->setUsers($user)
            ->setType('order_received')
            ->setFlush(false);

        $this->get('event_dispatcher')->dispatch('notification', $notificationEvent);
    }

    /**
     * @TODO Check with Jana why we generate sepa on each
     * Generate sepa mandate with new updated data
     * @param Request $request
     * @param Profile $profile
     * @param DebitAccount $debitAccount
     */
    private function generateSepaMandate(Request $request, Profile $profile, DebitAccount $debitAccount)
    {
        $fs = new Filesystem();
        $sepaMandate = new SepaMandate();
        $count = $profile->getSepaMandates()->count();
        $mandateReference = $profile->getId() . "-$count-THEAPOLIS";

        $sepaMandate->setProfile($profile);
        $sepaMandate->setIpAddress($request->getClientIp());
        $sepaMandate->setMandatereference($mandateReference);
        $sepaMandate->setPath("theaterjobs-" . $mandateReference . ".pdf");
        $profile->addSepaMandate($sepaMandate);

        $this->em->persist($sepaMandate);
        $this->em->persist($profile);

        if ($fs->exists($sepaMandate->getAbsolutePath())) {
            $fs->remove([$sepaMandate->getAbsolutePath()]);
        }

        $this->pdfGenerator->setOption('encoding', 'UTF-8');
        $this->pdfGenerator->generateFromHtml(
            $this->renderView(
                'TheaterjobsMembershipBundle:Pdf:currentSepaPdf.html.twig', [
                    'sepamandate' => $sepaMandate,
                    'debitAccount' => $debitAccount,
                ]
            ),
            $sepaMandate->getAbsolutePath()
        );
    }
}
