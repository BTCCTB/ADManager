<?php

namespace App\Tests\Service;

use App\Service\SmsInterface;
use App\Service\Twilio;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TwilioTest
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
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

    public function testSendSuccess()
    {
        $this->assertSame(Twilio::SEND, $this->twilio->send('test unique message', '+32477401458'));
    }

    public function testSendGroupSuccess()
    {
        $group = [
            '+32477401458',
            '+32477401458',
        ];
        $sendStatus = $this->twilio->sendGroup('test group message', $group);
        foreach ($sendStatus as $send) {
            $this->assertSame(Twilio::SEND, $send);
        }
    }
}
