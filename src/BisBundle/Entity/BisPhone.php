<?php

namespace BisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class BisPhone
 *
 * @package BisBundle\Entity
 * @ORM\Entity(repositoryClass="BisBundle\Repository\BisPhoneRepository")
 * @ORM\Table(name="view_bis_phone")
 * @UniqueEntity(fields={"id"})
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class BisPhone
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=true)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="nickname", type="string", length=100, nullable=true)
     */
    private $nickname;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="telephone", type="string", length=100, nullable=true)
     */
    private $telephone;

    /**
     * @var string
     *
     * @ORM\Column(name="sex", type="string", nullable=false)
     */
    private $sex;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=2, nullable=true)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="function", type="string", length=300, nullable=true)
     */
    private $function;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=100, nullable=true)
     */
    private $mobile;

    /**
     * @var BisCountry
     *
     * @ORM\ManyToOne(targetEntity="BisBundle\Entity\BisCountry", inversedBy="bisPhones")
     * @ORM\JoinColumn(name="country_workplace", referencedColumnName="cou_isocode_3letters", nullable=true)
     */
    private $countryWorkplace;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(String $email)
    {
        $this->email = $email;
        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(String $firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(String $lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active)
    {
        $this->active = $active;
        return $this;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(String $telephone)
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getSex(): string
    {
        return $this->sex;
    }

    public function setSex(String $sex)
    {
        $this->sex = $sex;
        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(String $language)
    {
        $this->language = $language;
        return $this;
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function setFunction(String $function)
    {
        $this->function = $function;
        return $this;
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }

    public function setMobile(String $mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    public function getCountryWorkplace(): BisCountry
    {
        return $this->countryWorkplace;
    }

    public function getCountry():  ? string
    {
        if ($this->getCountryWorkplace() !== null && $this->getCountryWorkplace() instanceof BisCountry) {
            return $this->getCountryWorkplace()->getCouIsocode3letters();
        }
        return null;
    }

    public function getCountryFlag() :  ? string
    {
        if ($this->getCountryWorkplace() !== null && $this->getCountryWorkplace() instanceof BisCountry) {
            return "<i class=\"flag-icon flag-icon-" . strtolower($this->getCountryWorkplace()->getCouIsocode2letters()) . "\" " .
            "title=\"" . $this->getCountryWorkplace()->getCouName() . "\" " .
            "alt=\"" . $this->getCountryWorkplace()->getCouName() . "\"></i>";
        }
        return null;
    }

    public function setCountryWorkplace(String $countryWorkplace)
    {
        $this->countryWorkplace = $countryWorkplace;
        return $this;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setType(String $type)
    {
        $this->type = $type;
        return $this;
    }

    public function getDisplayName(): string
    {
        if (!empty($this->getNickname())) {
            return strtoupper($this->getLastname()) . ', ' . ucfirst(strtolower($this->getNickname()));
        }
        return strtoupper($this->getLastname()) . ', ' . ucfirst(strtolower($this->getFirstname()));
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function setNickname(String $nickname)
    {
        $this->nickname = $nickname;
        return $this;
    }
}
