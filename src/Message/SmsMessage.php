<?php

namespace App\Message;

/**
 * Class SmsMessage
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class SmsMessage
{
    /**
     * @var string The content of the sms
     */
    private $content;

    /**
     * @var string The recipient of the sms
     */
    private $recipient;

    public function __construct(string $content, string $recipient)
    {
        $this->content = $content;
        $this->recipient = $recipient;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

}
