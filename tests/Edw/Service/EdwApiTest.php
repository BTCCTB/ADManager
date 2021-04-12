<?php

namespace Tests\Edw\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class EdwTest
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 * @group edw
 * @group external
 * @group api
 */
class EdwApiTest extends WebTestCase
{
    private $edw;

    public function setUp(): void
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();

        $this->edw = $container->get('test.edw_api');

        parent::setUp();
    }

    public function testGetToken()
    {
        $token = $this->edw->getToken();
        $this->assertNotFalse($token);
        $decodedToken = base64_decode($token);
        $this->assertStringContainsString('JWT', $decodedToken);
    }
}
