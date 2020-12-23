<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="account",indexes={@ORM\Index(name="active_idx", columns={"is_active"})})
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 *
 * @UniqueEntity(fields={"email"}, message="It looks this user has already an account")
 *
 * @Gedmo\Loggable(logEntryClass="LoggableEntry")
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class Account implements EntityInterface
{
    // Traits
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $employeeId;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $userPrincipalName;
    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     *
     * @Gedmo\Versioned
     *
     * @var string
     */
    private $accountName;
    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @Assert\Email()
     *
     * @Gedmo\Versioned
     *
     * @var string
     */
    private $email;
    /**
     * @ORM\Column(type="string")
     *
     * @Assert\Email()
     *
     * @Gedmo\Versioned
     *
     * @var string
     */
    private $emailContact;
    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     *
     * @Gedmo\Versioned
     *
     * @var string
     */
    private $firstname;
    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     *
     * @Gedmo\Versioned
     *
     * @var string
     */
    private $lastname;
    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     *
     * @Gedmo\Versioned
     *
     * @var string
     */
    private $token;
    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $generatedPassword;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $lastLoginAt = null;

    public function getId()
    {
        return $this->getEmployeeId();
    }

    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;

        return $this;
    }

    public function getUserPrincipalName()
    {
        return $this->userPrincipalName;
    }

    public function setUserPrincipalName($userPrincipalName)
    {
        $this->userPrincipalName = $userPrincipalName;

        return $this;
    }

    public function getAccountName()
    {
        return $this->accountName;
    }

    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getEmailContact()
    {
        return $this->emailContact;
    }

    public function setEmailContact($emailContact)
    {
        $this->emailContact = $emailContact;

        return $this;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    public function getGeneratedPassword()
    {
        return $this->generatedPassword;
    }

    public function setGeneratedPassword($generatedPassword)
    {
        $this->generatedPassword = $generatedPassword;

        return $this;
    }

    public function isActive()
    {
        return $this->isActive;
    }

    public function setActive($active)
    {
        $this->isActive = $active;

        return $this;
    }

    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt($lastLoginAt)
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function generateToken($userPrincipalName, $salt)
    {
        return base64_encode(
            base64_encode($userPrincipalName) .
            '|' .
            base64_encode($salt)
        );
    }

    public function getObjectClass()
    {
        return \get_class($this);
    }

    public function getObjectId()
    {
        return $this->getEmployeeId();
    }

    public function getIdentity()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    public static function cleanUpEmail($str, $charset = 'utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
        $str = preg_replace('#&[^;]+;#', '', $str);

        return $str;
    }
}
