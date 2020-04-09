<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageLogRepository")
 */
class MessageLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $recipient = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $multilanguage;

    /**
     * @ORM\Column(type="string", length=200)
     * @Assert\Length(
     *      min = 2,
     *      max = 160,
     *      minMessage = "Your message must be at least {{ limit }} characters long",
     *      maxMessage = "Your message cannot be longer than {{ limit }} characters",
     *      allowEmptyString = false
     * )
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 160,
     *      minMessage = "Your message must be at least {{ limit }} characters long",
     *      maxMessage = "Your message cannot be longer than {{ limit }} characters",
     *      allowEmptyString = true
     * )
     */
    private $messageFr;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 160,
     *      minMessage = "Your message must be at least {{ limit }} characters long",
     *      maxMessage = "Your message cannot be longer than {{ limit }} characters",
     *      allowEmptyString = true
     * )
     */
    private $messageNl;

    /**
     * @var User|null
     * @Gedmo\Blameable(on="create")
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @var \DateTime|null
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sendAt;

    public function __construct()
    {
        $this->setSendAt(new \DateTime());
    }

    public function getId():  ? int
    {
        return $this->id;
    }

    public function getSender() :  ? User
    {
        return $this->sender;
    }

    public function setSender(User $sender) : self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getMultilanguage()
    {
        return $this->multilanguage;
    }

    public function setMultilanguage($multilanguage)
    {
        $this->multilanguage = $multilanguage;

        return $this;
    }

    public function getMessage():  ? string
    {
        return $this->message;
    }

    public function setMessage(string $message) : self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessageFr()
    {
        return $this->messageFr;
    }

    public function setMessageFr($messageFr)
    {
        $this->messageFr = $messageFr;

        return $this;
    }

    public function getMessageNl()
    {
        return $this->messageNl;
    }

    public function setMessageNl($messageNl)
    {
        $this->messageNl = $messageNl;

        return $this;
    }

    public function getSendAt()
    {
        return $this->sendAt;
    }

    public function setSendAt(\DateTime $sendAt): self
    {
        $this->sendAt = $sendAt;

        return $this;
    }

    public static function recipientList()
    {
        return [
            'Group' => [
                'All Enabel' => 'all',
                'Enabel HQ' => 'hq',
                'Enabel Field' => 'field',
                'ResRep' => 'resrep',
            ],
            'Country' => [
                'Enabel RDC' => '180',
            ],
        ];
    }

    public function getRecipient():  ? array
    {
        return $this->recipient;
    }

    public function setRecipient(array $recipient) : self
    {
        $this->recipient = $recipient;

        return $this;
    }
}
