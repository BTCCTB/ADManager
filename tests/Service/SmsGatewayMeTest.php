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
        $this->assertSame(SmsGatewayMe::OK, $this->smsGatewayMe->configureApiClient());
    }

    public function testConfigurationWrongDevice()
    {
        $this->smsGatewayMe->setDeviceId(1234567890);
        $this->assertSame(SmsGatewayMe::INVALID_DEVICE_ID, $this->smsGatewayMe->configureApiClient());
    }

    public function testConfigurationWrongToken()
    {
        $this->smsGatewayMe->setApiToken('1234567890AZERTYUIOP');
        $this->assertSame(SmsGatewayMe::INVALID_TOKEN, $this->smsGatewayMe->configureApiClient());
    }

    public function testSendSuccess()
    {
        $this->assertSame(SmsGatewayMe::SEND, $this->smsGatewayMe->send('test unique message', '+32477401458'));
    }

    public function testSendWrongPhone()
    {
        $this->assertSame(SmsGatewayMe::INVALID_PHONE_NUMBER, $this->smsGatewayMe->send('test unique message', '+329999999999999999999999'));
    }

    public function testSendGroupSuccess()
    {
        $group = [
            '+32477401458',
            '+32477401458',
        ];
        $sendStatus = $this->smsGatewayMe->sendGroup('test group message', $group);
        foreach ($sendStatus as $send) {
            $this->assertSame(SmsGatewayMe::SEND, $send);
        }
    }

    public function testSendGroupWrongPhone()
    {
        $group = [
            '+3299999999999999999999999',
            '+3299999999999999999999999',
        ];
        $sendStatus = $this->smsGatewayMe->sendGroup('test group message', $group);
        foreach ($sendStatus as $send) {
            $this->assertSame(SmsGatewayMe::INVALID_PHONE_NUMBER, $send);
        }
    }
}
