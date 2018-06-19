<?php

namespace Theaterjobs\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MarketControllerTest extends WebTestCase {

    private $client;

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

    //Initialise property client
    //Call login function once
    public function setUp() {
        $this->client = static::createClient();
        $this->logIn();
    }

    //Function to test if we get needed information under market index.
    public function testIndex() {

        $this->client = static::createClient();
        $url = $this->client->getContainer()->get('router')->generate('tj_main_market_home', array(), false);
        $crawler = $this->client->request('GET', $url);


        $this->assertGreaterThan(
                0, $crawler->filter('html:contains("Market-Without-Organization")')->count()
        );
    }

    public function testNewAction() {
        $url = $this->client->getContainer()->get('router')->generate('tj_main_market_new', array(), false);
        $crawler = $this->client->request('GET', $url);
        //var_dump($this->client->getResponse()->getContent());
        $form = $crawler->selectButton('Publish')->form(array(
            'market[title]' => 'Test market',
            'market[categories]' => array(119, 120),
            'market[placeOfAction][is_localized]' => 0,
            'market[engagementStart]' => '2014-07-07',
            'market[engagementEnd]' => '2014-07-07',
            'market[applicationEnd]' => '2014-07-07',
            'market[publicationEnd]' => '2014-07-07',
            'market[description]' => 'test',
            'market[dateType]' => 1,
            'market[asap]' => 1
        ));

        $this->client->submit($form);
        $this->assertEquals('Theaterjobs\MainBundle\Controller\MarketController::createAction', $this->client->getRequest()->attributes->get('_controller'));

        $this->client->followRedirect();
        $this->assertEquals('Theaterjobs\MainBundle\Controller\MarketController::showAction', $this->client->getRequest()->attributes->get('_controller'));

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $market = $em->getRepository('TheaterjobsMainBundle:Market')->findOneBy(array('title' => 'Test market'));
        $this->assertNotNull($market);
        $em->remove($market);
        $em->flush();
    }

}
