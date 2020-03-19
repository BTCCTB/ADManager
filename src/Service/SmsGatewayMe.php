<?php

namespace App\Service;

use SMSGatewayMe\Client\ApiClient;
use SMSGatewayMe\Client\ApiException;
use SMSGatewayMe\Client\Api\DeviceApi;
use SMSGatewayMe\Client\Configuration;

/**
 * Class SmsGatewayMe
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class SmsGatewayMe
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
     * SmsGatewayMe constructor.
     *
     * @param null|string $apiToken API token for SMS Gateway Me
     * @param null|integer $deviceId The device id to send message
     */
    public function __construct( ? string $apiToken,  ? int $deviceId)
    {
        //TODO: Add env parameters to ansible/deploy
        $this->setApiToken($apiToken);
        $this->setDeviceId($deviceId);
    }

    /**
     * Send a message
     *
     * @param string $message The message
     * @param string $phoneNumber The recipient
     *
     * @return bool The status
     */
    public function send(string $message, string $phoneNumber) : bool
    {
        if (200 === $this->ConfigureApiClient()) {
            //TODO: Send a message [https://smsgateway.me/sms-api-documentation/messages/sending-a-sms-message]
            return true;
        }
        return false;
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
            $status[$phoneNumbers] = $this->send($message, $phoneNumber);
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
     * @return int Status code [200: Ok & 40x|500: Error]
     */
    public function ConfigureApiClient(): int
    {
        $config = Configuration::getDefaultConfiguration();
        $config->setApiKey('Authorization', $this->apiToken);

        $this->apiClient = new ApiClient($config);
        $deviceClient = new DeviceApi($this->apiClient);
        try {
            $deviceClient->getDevice($this->deviceId);
            return 200;
        } catch (ApiException $e) {
            return $e->getCode();
        }
    }
}
