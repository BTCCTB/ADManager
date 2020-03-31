<?php

namespace App\Service;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use SMSGatewayMe\Client\ApiClient;
use SMSGatewayMe\Client\ApiException;
use SMSGatewayMe\Client\Api\DeviceApi;
use SMSGatewayMe\Client\Api\MessageApi;
use SMSGatewayMe\Client\Configuration;
use SMSGatewayMe\Client\Model\SendMessageRequest;

/**
 * Class SmsGatewayMe
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class SmsGatewayMe implements SmsInterface
{

    /**
     * @var string
     */
    private $apiToken;

    /**
     * @var integer
     */
    private $deviceId;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @inheritDoc
     */
    public function __construct( ? string $accountId,  ? string $token,  ? string $from)
    {
        $this->setDeviceId((int) $accountId);
        $this->setApiToken($token);
    }

    /**
     * {@inheritDoc}
     */
    public function send(string $message, string $phoneNumber) : int
    {
        if (self::OK === $this->configureApiClient()) {
            try {
                $number = PhoneNumberUtil::getInstance()->parse($phoneNumber, PhoneNumberUtil::UNKNOWN_REGION);
                if (!PhoneNumberUtil::getInstance()->isValidNumber($number)) {
                    return self::INVALID_PHONE_NUMBER;
                }

                $messageClient = new MessageApi($this->apiClient);
                $messageRequest = new SendMessageRequest([
                    'phoneNumber' => PhoneNumberUtil::getInstance()->format($number, PhoneNumberFormat::E164),
                    'message' => $message,
                    'deviceId' => $this->deviceId,
                ]);
                $sendMessage = $messageClient->sendMessages([$messageRequest]);
                if (!empty($sendMessage) && $sendMessage[0]->getStatus() === 'pending') {
                    return self::SEND;
                }
            } catch (NumberParseException $e) {
                return self::INVALID_PHONE_NUMBER;
            }
        }
        return self::NOT_SEND;
    }

    /**
     * {@inheritDoc}
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
     * Set the Device ID
     *
     * @param integer $deviceId The device ID
     *
     * @return SmsInterface
     */
    public function setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    /**
     * Set the api token
     *
     * @param string $apiToken The api token
     *
     * @return SmsInterface
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * Configure the ApiClient
     *
     * @return int Status code
     */
    public function configureApiClient() : int
    {
        $config = Configuration::getDefaultConfiguration();
        $config->setApiKey('Authorization', $this->apiToken);

        $this->apiClient = new ApiClient($config);
        $deviceClient = new DeviceApi($this->apiClient);
        try {
            $deviceClient->getDevice($this->deviceId);
            return self::OK;
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    return self::OK;
                case 401:
                    return self::INVALID_TOKEN;
                case 400:
                    return self::INVALID_ACCOUNT_ID;
            }
        }
        return self::INVALID_REQUEST;
    }
}
