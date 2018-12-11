<?php

namespace Theaterjobs\ProfileBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SkillsControllerTest extends WebTestCase
{
    public function testGetremoteskills()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/getRemoteSkills');
    }

}
