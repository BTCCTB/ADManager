<?php

namespace Auth\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SecurityControllerTest
 *
 * @package Auth\Tests\Controller
 * @author  Damien Lagae <damien.lagae@enabel.be>
 * @coversDefaultClass \Auth\Controller\SecurityController
 * @group main
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
        $this->assertStringContainsString("Please login", $crawler->filter('.container h2')->text());
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
        $this->assertStringContainsString('Please login', $crawler->filter('.container h2')->text());
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
        $this->assertStringContainsString('Please login', $crawler->filter('.container h2')->text());
    }
}
