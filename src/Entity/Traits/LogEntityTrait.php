<?php

namespace App\Entity\Traits;

use App\Entity\User;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait LogEntityTrait
 *
 * @package App\Entity\Traits
 * @author  Damien Lagae <damienlagae@gmail.com>
 *
 * @property \App\Entity\User|null $user
 */
trait LogEntityTrait
{
    /**
     * @var DateTime
     * @ORM\Column(name="time", type="datetime", nullable=false)
     */
    protected $time;

    /**
     * @var DateTime
     * @ORM\Column(name="`date`", type="date", nullable=false)
     */
    protected $date;

    /**
     * @var string
     * @ORM\Column(name="agent", type="text", nullable=false)
     */
    protected $agent;

    /**
     * @var string
     * @ORM\Column(name="http_host", type="string", length=255, nullable=false)
     */
    protected $httpHost;

    /**
     * @var string
     * @ORM\Column(name="client_ip", type="string", length=255, nullable=false)
     */
    private $clientIp;

    /**
     * @return DateTime
     */
    public function getTime(): DateTime
    {
        return $this->time;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @return User|null
     */
    public function getUser():  ? User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getAgent() : string
    {
        return $this->agent;
    }

    /**
     * @return string
     */
    public function getHttpHost(): string
    {
        return $this->httpHost;
    }

    /**
     * @return string
     */
    public function getClientIp(): string
    {
        return $this->clientIp;
    }

    /**
     * @ORM\PrePersist()
     */
    protected function processTimeAndDate(): void
    {
        $now = new DateTime('NOW', new DateTimeZone('UTC'));

        $this->time = $this->time ?? $now;
        $this->date = $this->time ?? $now;
    }

    /**
     * @param Request $request
     */
    protected function processRequestData(Request $request): void
    {
        $this->clientIp = (string) $request->getClientIp();
        $this->httpHost = $request->getHttpHost();
        $this->agent = (string) $request->headers->get('User-Agent');
    }
}
