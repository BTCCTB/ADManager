<?php

namespace App\Tests\Service;

use App\Service\SmsGatewayMe;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SmsGatewayMeTest
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class SmsGatewayMeTest extends WebTestCase
{
    /**
     * @var SmsGatewayMe
     */
    private $smsGatewayMe;

    public function setUp(): void
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();

        $this->smsGatewayMe = $container->get('test.App\Service\SmsGatewayMe');

        parent::setUp();
    }

    public function testConfigurationSuccess()
    {
        $this->assertSame(200, $this->smsGatewayMe->ConfigureApiClient());
    }

    public function testConfigurationWrongDevice()
    {
        $this->smsGatewayMe->setDeviceId(1234567890);
        $this->assertSame(400, $this->smsGatewayMe->ConfigureApiClient());
    }

    public function testConfigurationWrongToken()
    {
        $this->smsGatewayMe->setApiToken('1234567890AZERTYUIOP');
        $this->assertSame(401, $this->smsGatewayMe->ConfigureApiClient());
    }

    public function testSendSuccess()
    {
        //TODO: everything
    }

    public function testSendFail()
    {
        //TODO: everything
    }

    public function testSendGroupSuccess()
    {
        //TODO: everything
    }

    public function testSendGroupFail()
    {
        //TODO: everything
    }
}
