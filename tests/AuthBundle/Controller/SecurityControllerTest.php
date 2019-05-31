<?php

namespace AuthBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest
 *
 * @package AuthBundle\Tests\Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 * @coversDefaultClass \AuthBundle\Controller\SecurityController
 */
class SecurityControllerTest extends WebTestCase
{
    /**
     * @covers ::loginAction()
     */
    public function testLoginAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains("Please login", $crawler->filter('.container h2')->text());
    }

    /**
     * @covers ::logoutAction()
     */
    public function testLogoutAction()
    {
        $client = static::createClient();
        $client->request('GET', '/logout');
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Please login', $crawler->filter('.container h2')->text());
    }

    /**
     * @covers ::redirectAction()
     */
    public function testRedirectAction()
    {
        $client = static::createClient();
        $client->request('GET', '/redirect');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect('/login'));
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Please login', $crawler->filter('.container h2')->text());
    }
}
