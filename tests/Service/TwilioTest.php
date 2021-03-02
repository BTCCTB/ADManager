<?php

namespace App\Tests\Service;

use App\Service\Exception\SmsException;
use App\Service\SmsInterface;
use App\Service\Twilio;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class TwilioTest
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 * @group external
 */
class TwilioTest extends KernelTestCase
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
        $this->expectException(SmsException::class);
//        $this->expectedExceptionCode(Twilio::INVALID_ACCOUNT_ID);
//        $this->expectedExceptionMessage('Twilio API: Invalid account ID');
        $this->twilio->configureApiClient();
    }

    public function testConfigurationWrongToken()
    {
        $this->twilio->setToken('1234567890AZERTYUIOP');
        $this->expectException(SmsException::class);
//        $this->expectedExceptionCode(Twilio::INVALID_TOKEN);
//        $this->expectedExceptionMessage('Twilio API: Invalid token');
        $this->twilio->configureApiClient();
    }

    public function testSendSuccess()
    {
        $this->assertSame(Twilio::SEND, $this->twilio->send('test unique message [Twilio]', '+32477401458'));
    }

    public function testSendWrongPhone()
    {
        $this->expectException(SmsException::class);
//        $this->expectedExceptionCode(Twilio::INVALID_PHONE_NUMBER);
//        $this->expectedExceptionMessage('Invalid phone number: The string supplied is too long to be a phone number.');
        $this->twilio->send('test unique message [Twilio]', '+329999999999999999999999');
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
        $this->expectException(SmsException::class);
//        $this->expectedExceptionCode(Twilio::INVALID_PHONE_NUMBER);
//        $this->expectedExceptionMessage('Invalid phone number: The string supplied is too long to be a phone number.');
        $this->twilio->sendGroup('test group message [Twilio]', $group);
    }
}
