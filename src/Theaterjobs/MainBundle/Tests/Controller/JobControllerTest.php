<?php

namespace Theaterjobs\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class JobCfontrollerTest extends WebTestCase {

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

    //Function to test if we get needed information under job index.
    public function testIndex() {

        $this->client = static::createClient();
        $url = $this->client->getContainer()->get('router')->generate('tj_main_job_home', array(), false);
        $crawler = $this->client->request('GET', $url);


        $this->assertGreaterThan(
                0, $crawler->filter('html:contains("Second-Job-For-Organization-Theater-Plauen-By-Userorgaother")')->count()
        );
    }

    //Testing if response from ajax is correct
    public function testAjaxIndex() {
        $url = $this->client->getContainer()->get('router')->generate('tj_main_job_index_home', array(), false);
        $crawler = $this->client->request('GET','http://theaterjobs21.local/app.php/en/job/json?page=1&category=2&type=0', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'));
        $json = json_decode($this->client->getResponse()->getContent(), true);
        var_dump($url);exit;
        $this->assertEquals($json[0]['title'], "First job for organization Theater Plauen by userOrgaOther");
    }

    //Create and fullfill job entry form to test if data is submited
    //To test image we must provide absolute path
    public function testNewAction() {
        $url = $this->client->getContainer()->get('router')->generate('tj_main_job_new', array(), false);
        $crawler = $this->client->request('GET', $url);
        $form = $crawler->selectButton('Save & request publishing')->form(array(
            'job[title]' => 'Test job',
            'job[categories]' => array(4, 3),
            'job[occupation]' => 1,
            'job[gratification]' => 1,
            'job[placeOfAction][is_localized]' => 0,
            'job[engagementStart]' => '2014-07-07',
            'job[engagementEnd]' => '2014-07-07',
            'job[applicationEnd]' => '2014-07-07',
            'job[publicationEnd]' => '2014-07-07',
            'job[description]' => 'test'
        ));

        $this->client->submit($form);
        $this->assertEquals('Theaterjobs\MainBundle\Controller\JobController::createAction', $this->client->getRequest()->attributes->get('_controller'));

        $this->client->followRedirect();
        $this->assertEquals('Theaterjobs\MainBundle\Controller\JobController::showAction', $this->client->getRequest()->attributes->get('_controller'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $job = $em->getRepository('TheaterjobsMainBundle:Job')->findOneBy(array('title' => 'Test job'));
        $this->assertNotNull($job);
    }

    //testing if job is deleted.In our case job is not really deleted but we set destroyed at under deleteAction in job controller
    public function testDeleteJob() {
        $url = $this->client->getContainer()->get('router')->generate('tj_main_job_show', array('slug' => 'test-job'), false);
        $crawler = $this->client->request('GET', $url);
        $form = $crawler->selectButton('Delete')->form();
        $this->client->submit($form);
        $this->assertEquals('Theaterjobs\MainBundle\Controller\JobController::deleteAction', $this->client->getRequest()->attributes->get('_controller'));

        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $job = $em->getRepository('TheaterjobsMainBundle:Job')->findOneBy(array('title' => 'Test job'));
        $this->assertNotNull($job->getDestroyedAt());
        $em->remove($job);
        $em->flush();
    }

    public function testJobTitleAutoComplete() {

        $url = $this->client->getContainer()->get('router')->generate('tj_main_job_autocomplete', array('query' => 'bass'), false);
        $crawler = $this->client->request('GET', $url, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'));
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertGreaterThan(1, count($json));
    }

    // TODO Implement test with selenium
    /*
      public function testJobLock() {

      $this->adminLogin('admin');
      $url = $this->client->getContainer()->get('router')->generate('tj_main_job_edit', array('slug' => 'Job-Without-Organization'), false);
      $this->client->request('GET', $url);

      $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

      $job = $em->getRepository('TheaterjobsMainBundle:Job')->findOneBy(array('slug' => 'Job-Without-Organization'));
      var_dump($job);
      $this->assertNotNull($job->getLockFirstTimestamp());
      }
     */

    // Tests creating a job as a template
    public function testTemplateAction() {
        $this->adminLogin('admin');
        $url = $this->client->getContainer()->get('router')->generate('tj_main_job_edit', array('slug' => 'Job-Without-Organization'), false);
        $crawler = $this->client->request('GET', $url);

        $form = $crawler->selectButton('Create from Template')->form();

        $crawler = $this->client->submit($form);
        $this->assertEquals('Theaterjobs\MainBundle\Controller\JobController::updateAction', $this->client->getRequest()->attributes->get('_controller'));

        $this->assertGreaterThan(
                0, $crawler->filter('html:contains("This job had been created based on")')->count()
        );
    }

    public function testJobIndexActionUser() {
        $this->userLogin();

        $url = $this->client->getContainer()->get('router')->generate('tj_main_job_home', array(), false);
        $crawler = $this->client->request('GET', $url);


        $this->assertEquals(
                0, $crawler->filter('tr')->eq(1)->filter('td')->eq(1)->filter(":contains('XXXX')")->count()
        );

        $this->assertEquals(
                1, $crawler->filter('tr')->eq(2)->filter('td')->eq(1)->filter(":contains('XXXX')")->count()
        );
    }

    public function testJobIndexActionMember() {
        $this->logIn();

        $url = $this->client->getContainer()->get('router')->generate('tj_main_job_home', array(), false);
        $crawler = $this->client->request('GET', $url);


        $this->assertEquals(
                0, $crawler->filter('tr')->eq(1)->filter('td')->eq(1)->filter(":contains('XXXX')")->count()
        );

        $this->assertEquals(
                0, $crawler->filter('tr')->eq(1)->filter('td')->eq(2)->filter(":contains('XXXX')")->count()
        );
    }

}
