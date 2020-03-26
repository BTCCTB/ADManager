<?php

namespace App\Service;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Twilio\Rest\Client;

/**
 * Class Twilio
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class Twilio implements SmsInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var String
     */
    private $from;

    public function __construct(Client $client, String $from)
    {
        $this->client = $client;
        $this->from = $from;
    }

    /**
     * @inheritDoc
     */
    public function send(string $message, string $phoneNumber): int
    {
        try {
            $number = PhoneNumberUtil::getInstance()->parse($phoneNumber, PhoneNumberUtil::UNKNOWN_REGION);
            if (!PhoneNumberUtil::getInstance()->isValidNumber($number)) {
                return self::INVALID_PHONE_NUMBER;
            }

            $sendMessage = $this->client->messages->create(
                $phoneNumber,
                [
                    'from' => $this->from,
                    'body' => $message,
                ]
            );

            if (!empty($sendMessage) && $sendMessage->status === 'sent') {
                return self::SEND;
            } else {
                error_log($sendMessage->errorMessage);
            }
        } catch (NumberParseException $e) {
            return self::INVALID_PHONE_NUMBER;
        }

        return self::NOT_SEND;
    }
/**
 * @inheritDoc
 */
    public function sendGroup(string $message, array $phoneNumbers): array
    {
        // TODO: Implement sendGroup() method.
    }
}
