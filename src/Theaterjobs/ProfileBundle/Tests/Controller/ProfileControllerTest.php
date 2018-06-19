<?php

namespace Theaterjobs\ProfileBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ProfileControllerTest extends WebTestCase {

    private $client;

    //Initialise property client
    //Call login function once
    public function setUp() {
        $this->client = static::createClient();
        $this->logIn();
    }

    //Helper function to keep login
    private function logIn() {

        $session = $this->client->getContainer()->get('session');

        $userProvider = $this->client->getContainer()->get('fos_user.user_provider.username_email');
        $user = $userProvider->loadUserByUsername('member');

        $token = new UsernamePasswordToken($user, null, 'main', array('ROLE_MEMBER'));
        $session->set('_security_' . 'main', serialize($token));
        $this->client->getContainer()->get('security.token_storage')->setToken($token);
        $this->client->getContainer()->get('session')->set('jobrules_accepted', true);
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function adminLogin($username) {

        $session = $this->client->getContainer()->get('session');

        $userProvider = $this->client->getContainer()->get('fos_user.user_provider.username_email');
        $user = $userProvider->loadUserByUsername($username);

        $token = new UsernamePasswordToken($user, null, 'main', array('ROLE_ADMIN'));
        $session->set('_security_' . 'main', serialize($token));
        $this->client->getContainer()->get('security.token_storage')->setToken($token);
        $this->client->getContainer()->get('session')->set('jobrules_accepted', true);
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function userLogin() {
        $session = $this->client->getContainer()->get('session');

        $userProvider = $this->client->getContainer()->get('fos_user.user_provider.username_email');
        $user = $userProvider->loadUserByUsername('user');

        $token = new UsernamePasswordToken($user, null, 'main', array('ROLE_USER'));
        $session->set('_security_' . 'main', serialize($token));
        $this->client->getContainer()->get('security.token_storage')->setToken($token);
        $this->client->getContainer()->get('session')->set('jobrules_accepted', true);
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testWizardOnFirstVisit() {
        $this->logIn();

        $url = $this->client->getContainer()->get('router')->generate('tj_profile_profile_show', array('slug' => 'Member-Ship'), false);
        $crawler = $this->client->request('GET', $url);

        $this->assertEquals('Theaterjobs\ProfileBundle\Controller\ProfileController::showAction', $this->client->getRequest()->attributes->get('_controller'));

        $crawler = $this->client->followRedirect();

        $this->assertEquals('Theaterjobs\ProfileBundle\Controller\ProfileController::editAction', $this->client->getRequest()->attributes->get('_controller'));

        $form = $crawler->selectButton('Save')->form(array(
            "profile[birthPlace]" => 'Germany'
        ));

        $this->client->submit($form);
        $this->assertEquals('Theaterjobs\ProfileBundle\Controller\ProfileController::updateAction', $this->client->getRequest()->attributes->get('_controller'));

        $crawler = $this->client->followRedirect();

        $this->assertEquals('Theaterjobs\ProfileBundle\Controller\ProfileController::showAction', $this->client->getRequest()->attributes->get('_controller'));

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $profile = $em->getRepository('TheaterjobsProfileBundle:Profile')->findOneBy(array('slug' => 'Member-Ship'));

        $this->assertEquals(1, $profile->getShowWizard());

        $this->assertGreaterThan(
                0, $crawler->filter('title:contains("Member")')->count()
        );
    }

    public function testProfileIsNotEditable() {
        $this->userLogin();

        $url = $this->client->getContainer()->get('router')->generate('tj_profile_profile_show', array('slug' => 'Member-Ship'), false);
        $crawler = $this->client->request('GET', $url);

        $this->assertEquals('Theaterjobs\ProfileBundle\Controller\ProfileController::showAction', $this->client->getRequest()->attributes->get('_controller'));

        $this->assertEquals(
                0, $crawler->filter('button:contains("Edit")')->count()
        );
    }

    public function testProfileIsEditable() {
        $this->logIn();

        $url = $this->client->getContainer()->get('router')->generate('tj_profile_profile_show', array('slug' => 'Member-Ship'), false);
        $crawler = $this->client->request('GET', $url);

        $this->assertEquals('Theaterjobs\ProfileBundle\Controller\ProfileController::showAction', $this->client->getRequest()->attributes->get('_controller'));

        $this->assertGreaterThan(
                0, $crawler->filter('button')->count()
        );
    }

    public function tearDown() {

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $profile = $em->getRepository('TheaterjobsProfileBundle:Profile')->findOneBy(array('slug' => 'Member-Ship'));
        $em->persist($profile->setShowWizard(0));
        $em->flush();

        parent::tearDown();
    }

}
