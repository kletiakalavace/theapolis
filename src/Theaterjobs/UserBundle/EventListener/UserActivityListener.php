<?php

namespace Theaterjobs\UserBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Doctrine\ORM\EntityManager;
use Theaterjobs\InserateBundle\Entity\Education;
use Theaterjobs\InserateBundle\Entity\Job;
use Theaterjobs\InserateBundle\Entity\Network;
use Theaterjobs\UserBundle\Entity\UserActivity;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * StatsSubscriber
 *
 * @category EventListener
 * @package  Theaterjobs\StatsBundle\EventListener
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 * @DI\Service("theaterjobs_user.user_activity_listener")
 */
class UserActivityListener {

    /**
     * @var AuthorizationChecker;
     */
    private $securityContext;

    /** @var TokenStorage $tokenStorage */
    private $tokenStorage;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Router
     */
    private $router;

    /**
     * @DI\InjectParams({
     *     "securityContext" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "router" = @DI\Inject("router")
     * })
     * @param AuthorizationChecker $securityContext
     * @param TokenStorage $tokenStorage
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(AuthorizationChecker $securityContext, TokenStorage $tokenStorage, EntityManager $em, Router $router) {
        $this->securityContext = $securityContext;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * @DI\Observe("UserActivityEvent", priority = 255)
     */
    public function onUserActivity(\Theaterjobs\UserBundle\Event\UserActivityEvent $event) {

        $user = null;
        try{

            $user = ($this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) ?
                $this->tokenStorage->getToken()->getUser() : null;
        }catch(AuthenticationCredentialsNotFoundException $e) {
            if ($event->getUser()){
                $user = $event->getUser();
            }else{
                throw $e;
            }
        }

        if ($event->getUserActivity() != $user) {
            $data = $this->generateText($event->getUserActivity(), $event->getText(), $event->getForAdmin());
        } else {
            $data = $this->generateText($event->getUserActivity(), $event->getText(), $event->getForAdmin(), $user);
        }
        $userActivity = new UserActivity();

        if (strpos(get_class($event->getUserActivity()), '\UserBundle\Entity\User')) {
            $userActivity->setUser($event->getUserActivity());
        } else {
            $userActivity->setUser($user);
        }

        if ($event->getChangedFields() !== null) {
            $userActivity->setChangedFields($event->getChangedFields());
        }
        
        $userActivity->setCreatedBy($user);

        if (strpos(get_class($event->getUserActivity()), '\NewsBundle\Entity\Replies')) {

            $userActivity->setCreatedAt(new \DateTime());
            $userActivity->setActivityText($event->getText() . '<div class="well well-sm">' . substr($event->getUserActivity()->getComment(), 0, 100) . '</div>');
            $userActivity->setEntityClass(get_class($event->getUserActivity()));
            $userActivity->setEntityId($event->getUserActivity()->getId());
        } else {

            $userActivity->setActivityText($data['str']);
            $userActivity->setCreatedAt(new \DateTime());
            $userActivity->setEntityClass($data['class']);
            $userActivity->setEntityId($data['id']);
        }

        $userActivity->setAdminOnly($data['admin']);
        $this->em->persist($userActivity);
        $this->em->flush();
    }

    public function generateText($object, $action, $forAdmin, \Theaterjobs\UserBundle\Entity\User $user = null) {
        $objectClass = get_class($object);
        $data = [];
        $str = '';
        if (strpos($objectClass, '\NewsBundle\Entity\News') !== false) {
            $data['str'] = $action . ' <a href="' . $this->router->generate('tj_news_show', array('slug' => $object->getSlug())) . '">' . $object->getTitle() . '</a>';
            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = false;
        }
        if (strpos($objectClass, '\NewsBundle\Entity\Replies')) {
            $str.=$action . ' <a href="' . $this->router->generate('tj_news_show', array('slug' => $object->getNews()->getSlug())) . '">' . $object->getNews()->getTitle() . '</a>';
            $str.='<div class="well well-sm">' . substr($object->getComment(), 0, 100) . '</div>';
            $data['str'] = $str;
            $data['id'] = $object->getNews()->getId();
            $data['class'] = get_class($object->getNews());
            $data['admin'] = false;
        }

        if (strpos($objectClass, '\InserateBundle\Entity\Organization')) {
//            NOTE: commented under admins request to remove organization name from logs 31.7.2017
//            $data['str'] = $action . ' <a href="' . $this->router->generate('tj_organization_show', array('slug' => $object->getSlug())) . '">' . $object->getName() . '</a>';

            $data['str'] = $action;
            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = false;
        }

        if (strpos($objectClass, '\UserBundle\Entity\EmailChangeRequest')) {

            $data['str'] = $action;
            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = false;
        }
        
        if (strpos($objectClass, '\ProfileBundle\Entity\Productions')) {

            $data['str'] = $action;
            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = false;
        }
        
        if (strpos($objectClass, '\ProfileBundle\Entity\Employments')) {

            $data['str'] = $action;
            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = false;
        }

        if (strpos($objectClass, '\ProfileBundle\Entity\Profile')) {

            $data['str'] = $action;
            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = false;
        }

        if (strpos($objectClass, '\InserateBundle\Entity\AdminComments')) {

            $education = new Education();
            $job = new Job();
            $network = new Network();

            // If Inserate has an Object type Job
            if ($object->getInserate() && ( $object->getInserate() instanceof $job )) {
                $str.=$action . ' <a href="' . $this->router->generate('tj_inserate_job_route_show', array('slug' => $object->getInserate()->getSlug())) . '">' . $object->getInserate()->getTitle() . '</a>';
                $str.='<div class="well well-sm">' . substr($object->getDescription(), 0, 100) . '</div>';
                $data['str'] = $str;
                $data['id'] = $object->getInserate()->getId();
                $data['class'] = get_class($object->getInserate());
                $data['admin'] = true;
                // If Inserate has an Object type Education
            } elseif ($object->getInserate() && ( $object->getInserate() instanceof $education )) {
                $str.=$action . ' <a href="' . $this->router->generate('tj_inserate_education_route_show', array('slug' => $object->getInserate()->getSlug())) . '">' . $object->getInserate()->getTitle() . '</a>';
                $str.='<div class="well well-sm">' . substr($object->getDescription(), 0, 100) . '</div>';
                $data['str'] = $str;
                $data['id'] = $object->getInserate()->getId();
                $data['class'] = get_class($object->getInserate());
                $data['admin'] = true;
            } elseif ($object->getInserate() && ( $object->getInserate() instanceof $network )) {
                $str.=$action . ' <a href="' . $this->router->generate('tj_inserate_network_route_show', array('slug' => $object->getInserate()->getSlug())) . '">' . $object->getInserate()->getTitle() . '</a>';
                $str.='<div class="well well-sm">' . substr($object->getDescription(), 0, 100) . '</div>';
                $data['str'] = $str;
                $data['id'] = $object->getInserate()->getId();
                $data['class'] = get_class($object->getInserate());
                $data['admin'] = true;
            }

            if ($object->getOrganization()) {
                $str.=$action . ' <a href="' . $this->router->generate('tj_organization_show', array('slug' => $object->getOrganization()->getSlug())) . '">' . $object->getOrganization()->getName() . '</a>';
                $str.='<div class="well well-sm">' . substr($object->getDescription(), 0, 100) . '</div>';
                $data['str'] = $str;
                $data['id'] = $object->getOrganization()->getId();
                $data['class'] = get_class($object->getOrganization());
                $data['admin'] = true;
            }
        }

        if (strpos($objectClass, '\InserateBundle\Entity\Education')) {
            $data['str'] = $action . ' <a href="' . $this->router->generate('tj_inserate_education_route_show', array('slug' => $object->getSlug())) . '">' . $object->getTitle() . '</a>';
            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = $forAdmin;
        }

        if (strpos($objectClass, '\InserateBundle\Entity\Network')) {
            $data['str'] = $action . ' <a href="' . $this->router->generate('tj_inserate_network_route_show', array('slug' => $object->getSlug())) . '">' . $object->getTitle() . '</a>';
            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = $forAdmin;
        }

        if (strpos($objectClass, '\InserateBundle\Entity\Job')) {
            $data['str'] = $action . ' <a href="' . $this->router->generate('tj_inserate_job_route_show', array('slug' => $object->getSlug())) . '">' . $object->getTitle() . '</a>';
            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = $forAdmin;
        }

        if (strpos($objectClass, '\CategoryBundle\Entity\Category')) {
            $data['str'] = $action . ' ' . $object->getTitle();
            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = false;
        }

        if (strpos($objectClass, '\UserBundle\Entity\User')) {
            if ($object != $user) {
                $data['str'] = $action . ' <a href="' . $this->router->generate('tj_profile_profile_show', array('slug' => $object->getProfile()->getSlug())) . '">' . $object->getProfile()->getFirstName() . ' ' . $object->getProfile()->getLastName() . '</a>';
            } else {
                $data['str'] = $action;
            }

            $data['id'] = $object->getId();
            $data['class'] = get_class($object);
            $data['admin'] = false;
        }


        if (strpos($objectClass, '\MainBundle\Entity\Complain')) {
            $qre = $this->em->createQueryBuilder();
            if ($object->getEntityType() === 'job') {
                $entity = $qre->select("j")
                                ->from("TheaterjobsInserateBundle:Job", "j")
                                ->where("j.id= :id")
                                ->setParameter('id', $object->getEntityId())
                                ->getQuery()->getResult();
                $data['class'] = "Theaterjobs\InserateBundle\Entity\Job";
                $text = ' on job <a href="' . $this->router->generate('tj_inserate_job_route_show', array('slug' => $entity[0]->getSlug())) . '">' . $entity[0]->getTitle() . '</a> with text';
            } elseif ($object->getEntityType() === 'network') {
                $entity = $qre->select("n")
                                ->from("TheaterjobsInserateBundle:Network", "n")
                                ->where('n.id= :id')
                                ->setParameter('id', $object->getEntityId())
                                ->getQuery()->getResult();
                $data['class'] = "Theaterjobs\InserateBundle\Entity\Network";
                $text = ' on network <a href="' . $this->router->generate('tj_inserate_network_route_show', array('slug' => $entity[0]->getSlug())) . '">' . $entity[0]->getTitle() . '</a> with text';
            } elseif ($object->getEntityType() === 'education') {
                $entity = $qre->select("e")
                                ->from("TheaterjobsInserateBundle:Education", "e")
                                ->where('e.id= :id')
                                ->setParameter('id', $object->getEntityId())
                                ->getQuery()->getResult();
                $data['class'] = "Theaterjobs\InserateBundle\Entity\Education";
                $text = ' on education <a href="' . $this->router->generate('tj_inserate_education_route_show', array('slug' => $entity[0]->getSlug())) . '">' . $entity[0]->getTitle() . '</a> with text';
            }  elseif ($object->getEntityType() === 'profile') {
                $entity = $qre->select("p")
                                ->from("TheaterjobsProfileBundle:Profile", "p")
                                ->where("p.id= :id")
                                ->setParameter('id', $object->getEntityId())
                                ->getQuery()->getResult();
                $data['class'] = "Theaterjobs\ProfileBundle\Entity\Profile";
                $text = ' on profile <a href="' . $this->router->generate('tj_profile_profile_show', array('slug' => $entity[0]->getSlug())) . '">' . $entity[0]->getFirstName() . ' ' . $entity[0]->getLastName() . '</a> with text';
            }
                        
            $data['str'] = $action . ' ' . $text . ' ' . $object->getText();
            $data['id'] = $object->getEntityId();
            $data['admin'] = true;
        }

        return $data;
    }

}
