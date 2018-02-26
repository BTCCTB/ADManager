<?php

namespace AuthBundle\Service;

/**
 * Class ActiveDirectoryResponse
 *
 * @package AuthBundle\Service
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class ActiveDirectoryResponse
{
    /**
     * @var String
     */
    private $message;
    /**
     * @var int
     */
    private $status;
    /**
     * @var array
     */
    private $data;
    /**
     * @var int
     */
    private $type;

    public function __construct(String $message, int $status = ActiveDirectoryResponseStatus::DONE, int $type = ActiveDirectoryResponseType::GENERAL, array $data = [])
    {

        $this->message = $message;
        $this->status = $status;
        $this->data = $data;
        $this->type = $type;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
