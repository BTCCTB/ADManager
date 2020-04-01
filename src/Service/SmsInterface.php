<?php

namespace App\Service;

/**
 * Class SmsGatewayMe
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
interface SmsInterface
{
    const OK = 200;
    const SEND = 202;
    const INVALID_REQUEST = 400;
    const INVALID_TOKEN = 401;
    const INVALID_PHONE_NUMBER = 404;
    const INVALID_PHONE_NUMBER_TYPE = 406;
    const NOT_SEND = 409;
    const INVALID_ACCOUNT_ID = 412;

    /**
     * SmsInterface constructor.
     *
     * @param null|string $accountId The account id of the SMS services
     * @param null|string  $token The token for SMS services
     * @param null|string  $from      Phone number used for sending
     */
    public function __construct(? String $accountId, ? String $token, ? String $from);

    /**
     * Send a message
     *
     * @param string $message     The message
     * @param string $phoneNumber The recipient
     *
     * @return integer The status code
     */
    public function send(string $message, string $phoneNumber) : int;

    /**
     * Send a message to a group
     *
     * @param string   $message      The message
     * @param string[] $phoneNumbers The list of recipients
     *
     * @return array The status for each recipient
     */
    public function sendGroup(string $message, array $phoneNumbers) : array;
}
