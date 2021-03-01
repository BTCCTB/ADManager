<?php

namespace App\Service;

use App\Service\Exception\SmsException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\RestException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

/**
 * Class Twilio
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 * @see \App\Tests\Service\TwilioTest
 */
class Twilio implements SmsInterface
{
    /**
     * @var String
     */
    private $from;

    /**
     * @var string|null
     */
    private $accountId;

    /**
     * @var string|null
     */
    private $token;

    /**
     * @var Client
     */
    private $apiClient;

    /**
     * {@inheritDoc}
     */
    public function __construct(? String $accountId, ? String $token, ? String $from)
    {
        $this->setSID($accountId);
        $this->setToken($token);
        $this->from = $from;
    }

    /**
     * @inheritDoc
     * @throws SmsException
     */
    public function send(string $message, string $phoneNumber) : int
    {
        if (self::OK === $this->configureApiClient()) {
            try {
                /** @var PhoneNumber $number */
                $number = PhoneNumberUtil::getInstance()->parse($phoneNumber);

                if (!PhoneNumberUtil::getInstance()->isValidNumber($number)) {
                    throw new SmsException('Invalid phone number: "' . $phoneNumber . '"', self::INVALID_PHONE_NUMBER);
                }
                $phoneNumber = '+' . $number->getCountryCode() . $number->getNationalNumber();

                // Create & send message
                $sendMessage = $this->apiClient->messages->create(
                    $phoneNumber,
                    [
                        'from' => $this->from,
                        'body' => $message,
                    ]
                );

                // Get message info (status)
                $message = $this->apiClient->messages($sendMessage->sid)->fetch();

                if (!empty($message) && $message->status === 'sent') {
                    return self::SEND;
                }
            } catch (NumberParseException $e) {
                throw new SmsException('Invalid phone number: ' . $e->getMessage(), self::INVALID_PHONE_NUMBER);
            } catch (TwilioException | RestException $e) {
                throw new SmsException($e->getMessage(), self::NOT_SEND);
            }
        }

        return self::NOT_SEND;
    }

    /**
     * @inheritDoc
     */
    public function sendGroup(string $message, array $phoneNumbers) : array
    {
        $status = [];

        foreach ($phoneNumbers as $phoneNumber) {
            $status[$phoneNumber] = $this->send($message, $phoneNumber);
        }

        return $status;
    }

    /**
     * Configure the ApiClient
     *
     * @return int Status code
     * @throws SmsException
     */
    public function configureApiClient() : int
    {
        try {
            $this->apiClient = new Client($this->accountId, $this->token);
            $this->apiClient->messages->read([], 20);
            return self::OK;
        } catch (RestException | ConfigurationException | TwilioException $e) {
            switch ($e->getCode()) {
                case 200:
                    return self::OK;
                case 20003:
                    throw new SmsException('Twilio API: Invalid token', self::INVALID_TOKEN);
                case 20404:
                    throw new SmsException('Twilio API: Invalid account ID', self::INVALID_ACCOUNT_ID);
            }
        }
        throw new SmsException('Twilio API: Invalid request', self::INVALID_REQUEST);
    }

    public function setSID($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }
}
