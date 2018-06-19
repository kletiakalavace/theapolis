<?php

namespace Theaterjobs\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class GeneralTest extends WebTestCase {

    private $client;

    //Initialise property client
    //Call login function once
    /**
     * Init function to test
     */
    public function setUp() {
        $this->client = static::createClient();
        $this->adminLogin('admin');
        $this->homePage();
        // @TODO Implment these page tests for overall page test
        $this->peoplePage();
        $this->organizationPage();
        $this->workPage();
        $this->newsPage();
        $this->profilePage('admin-istrator');
        $this->messagesPage();
        $this->accountSettingsPage();

    }

    /**
     * Login functon for member
     * @param string $username
     */
    private function memberLogIn($username) {

        $session = $this->client->getContainer()->get('session');

        $userProvider = $this->client->getContainer()->get('fos_user.user_provider.username_email');
        $user = $userProvider->loadUserByUsername($username);

        $token = new UsernamePasswordToken($user, null, 'main', array('ROLE_MEMBER'));
        $session->set('_security_' . 'main', serialize($token));
        $this->client->getContainer()->get('security.token_storage')->setToken($token);
        $this->client->getContainer()->get('session')->set('jobrules_accepted', true);
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
    /**
     * Login functon for admin
     * @param string $username
     */
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

    /**
     * Login functon for user
     * @param string $username
     */
    private function userLogin($username) {
        $session = $this->client->getContainer()->get('session');

        $userProvider = $this->client->getContainer()->get('fos_user.user_provider.username_email');
        $user = $userProvider->loadUserByUsername($username);

        $token = new UsernamePasswordToken($user, null, 'main', array('ROLE_USER'));
        $session->set('_security_' . 'main', serialize($token));
        $this->client->getContainer()->get('security.token_storage')->setToken($token);
        $this->client->getContainer()->get('session')->set('jobrules_accepted', true);
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }


    /**
     * Check home page dashboard
     */
    public function homePage()
    {
        $this->client = static::createClient();
        $url = $this->client->getContainer()->get('router')->generate('tj_main_dashboard_index');
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(200, $crawler->getResponse()->getStatusCode());
    }
    /**
     * Check organization page
     */
    public function organizationPage()
    {
        $this->client = static::createClient();
        $url = $this->client->getContainer()->get('router')->generate('tj_main_organization_home');
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(200, $crawler->getResponse()->getStatusCode());
    }
    /**
     * Check work page
     */
    public function workPage()
    {
        $this->client = static::createClient();
        $url = $this->client->getContainer()->get('router')->generate('tj_inserate_job_route_home');
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(200, $crawler->getResponse()->getStatusCode());
    }
    /**
     * Check people page
     */
    public function peoplePage()
    {
        $this->client = static::createClient();
        $url = $this->client->getContainer()->get('router')->generate('tj_profile_profile_index');
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(200, $crawler->getResponse()->getStatusCode());
    }
    /**
     * Check news page
     */
    public function newsPage()
    {
        $this->client = static::createClient();
        $url = $this->client->getContainer()->get('router')->generate('tj_news');
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(200, $crawler->getResponse()->getStatusCode());
    }
    /**
     * Check profile page
     */
    public function profilePage($slug)
    {
        $this->client = static::createClient();
        $url = $this->client->getContainer()->get('router')->generate('tj_profile_profile_show', ['slug' => $slug]);
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(200, $crawler->getResponse()->getStatusCode());
    }
    /**
     * Check messages page
     */
    public function messagesPage()
    {
        $this->client = static::createClient();
        $url = $this->client->getContainer()->get('router')->generate('tj_message_index');
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(200, $crawler->getResponse()->getStatusCode());
    }
    /**
     * Check account Settings page
     */
    public function accountSettingsPage()
    {
        $this->client = static::createClient();
        $url = $this->client->getContainer()->get('router')->generate('tj_message_index');
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(200, $crawler->getResponse()->getStatusCode());
    }
}
