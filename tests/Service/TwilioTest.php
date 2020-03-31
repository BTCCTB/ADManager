<?php

namespace App\Tests\Service;

use App\Service\SmsInterface;
use App\Service\Twilio;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TwilioTest
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 * @group SMS
 */
class TwilioTest extends WebTestCase
{
    /**
     * @var SmsInterface
     */
    private $twilio;

    public function setUp(): void
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();

        $this->twilio = $container->get('test.App\Service\Twilio');

        parent::setUp();
    }

    public function testConfigurationSuccess()
    {
        $this->assertSame(Twilio::OK, $this->twilio->configureApiClient());
    }

    public function testConfigurationWrongAccount()
    {
        $this->twilio->setSID('1234567890');
        $this->assertSame(Twilio::INVALID_ACCOUNT_ID, $this->twilio->configureApiClient());
    }

    public function testConfigurationWrongToken()
    {
        $this->twilio->setToken('1234567890AZERTYUIOP');
        $this->assertSame(Twilio::INVALID_TOKEN, $this->twilio->configureApiClient());
    }

    public function testSendSuccess()
    {
        $this->assertSame(Twilio::SEND, $this->twilio->send('test unique message [Twilio]', '+32477401458'));
    }

    public function testSendWrongPhone()
    {
        $this->assertSame(Twilio::INVALID_PHONE_NUMBER, $this->twilio->send('test unique message [Twilio]', '+329999999999999999999999'));
    }

    public function testSendGroupSuccess()
    {
        $group = [
            '+32477401458',
            '+32477401458',
        ];
        $sendStatus = $this->twilio->sendGroup('test group message [Twilio]', $group);
        foreach ($sendStatus as $send) {
            $this->assertSame(Twilio::SEND, $send);
        }
    }

    public function testSendGroupWrongPhone()
    {
        $group = [
            '+3299999999999999999999999',
            '+3299999999999999999999999',
        ];
        $sendStatus = $this->twilio->sendGroup('test group message [Twilio]', $group);
        foreach ($sendStatus as $send) {
            $this->assertSame(Twilio::INVALID_PHONE_NUMBER, $send);
        }
    }
}
