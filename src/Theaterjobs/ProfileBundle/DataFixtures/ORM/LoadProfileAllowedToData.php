<?php

namespace Theaterjobs\ProfileBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Theaterjobs\ProfileBundle\Entity\ProfileAllowedTo;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Datafixtures for the profile eye color.
 *
 * @category DataFixtures
 * @package  Theaterjobs\MainBundle\DataFixtures\ORM
 * @author   Malvin Dake
 * @author   Vilson Duka
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.theaterjobs.de
 */
class LoadProfileAllowedToData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * The container
     *
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * Set the container interface
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    /**
     * Load the fixtures
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager) {
        
        $em = $this->container->get('doctrine')->getEntityManager('default');
        
        $profiles =  $em->getRepository('TheaterjobsProfileBundle:Profile')->findAll();

        foreach ($profiles as $profile) {
            $profileAllowedTo = new ProfileAllowedTo();
            $profileAllowedTo->setCommentInNews(true);
            $profileAllowedTo->setPublishJob(true);
            $profileAllowedTo->setWriteToForum(true);
            $profile->setProfileAllowedTo($profileAllowedTo);
            $manager->persist($profileAllowedTo);
            $manager->persist($profile);
            $manager->flush();
            $this->setReference("profileAllowedTo_$profile", $profileAllowedTo);
        }
    }

    /**
     * Get the order.
     *
     * @return int $order
     */
    public function getOrder() {
        return 20;
    }

}

