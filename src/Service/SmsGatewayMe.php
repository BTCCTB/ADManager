<?php

namespace App\Service;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
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
class SmsGatewayMe
{
    const OK = 200;
    const SEND = 202;
    const INVALID_REQUEST = 400;
    const INVALID_TOKEN = 401;
    const INVALID_PHONE_NUMBER = 404;
    const INVALID_PHONE_NUMBER_TYPE = 406;
    const NOT_SEND = 409;
    const INVALID_DEVICE_ID = 412;

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
     * SmsGatewayMe constructor.
     *
     * @param null|string $apiToken API token for SMS Gateway Me
     * @param null|integer $deviceId The device id to send message
     */
    public function __construct( ? string $apiToken,  ? int $deviceId)
    {
        $this->setApiToken($apiToken);
        $this->setDeviceId($deviceId);
    }

    /**
     * Send a message
     *
     * @param string $message The message
     * @param string $phoneNumber The recipient
     *
     * @return integer The status code
     */
    public function send(string $message, string $phoneNumber) : int
    {
        if (self::OK === $this->ConfigureApiClient()) {
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
     * Send a message to a group
     *
     * @param string $message The message
     * @param string[]  $phoneNumbers The list of recipients
     *
     * @return array The status for each recipient
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
     * @return $this
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
     * @return $this
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
    public function ConfigureApiClient(): int
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
                    return self::INVALID_DEVICE_ID;
            }
        }
        return self::INVALID_REQUEST;
    }
}
