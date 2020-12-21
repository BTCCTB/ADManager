<?php

namespace App\MessageHandler;

use App\Message\SmsMessage;
use App\Service\SmsGatewayMe;
use App\Service\SmsInterface;
use App\Service\Twilio;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class SmsMessageHandler
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class SmsMessageHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var SmsInterface
     */
    private $smsService;

    public function __construct(Twilio $smsService)
    {
        $this->smsService = $smsService;
    }

    public function __invoke(SmsMessage $message)
    {
        if ($this->logger) {
            $this->logger->info(sprintf("Try to send a sms to %s", $message->getRecipient()));
        }
        $status = $this->smsService->send($message->getContent(), $message->getRecipient());
        if ($status != SmsInterface::SEND) {
            if ($this->logger) {
                $this->logger->alert(sprintf("Sms can't be send to %s (status: %s)", $message->getRecipient(), $status));
            }
            throw new \Exception(sprintf("Sms can't be send to %s (status: %s)", $message->getRecipient(), $status));
        } else {
            if ($this->logger) {
                $this->logger->info(sprintf("Sms send to %s", $message->getRecipient()));
            }
        }
    }
}
