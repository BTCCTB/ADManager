<?php

namespace BisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class BisPersonView
 *
 * @package BisBundle\Entity
 * @ORM\Entity(repositoryClass="BisPersonViewRepository")
 * @ORM\Table(name="bis_person_view")
 * @UniqueEntity(fields={"per_email"})
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class BisPersonView
{
    const MAIN_DOMAIN = '@enabel.be';
    /**
     * @var integer
     *
     * @ORM\Column(name="per_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $perId;

    /**
     * @var string
     *
     * @ORM\Column(name="per_email", type="string", length=100, nullable=true)
     */
    private $perEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="per_firstname", type="string", length=100, nullable=true)
     */
    private $perFirstname;

    /**
     * @var string
     *
     * @ORM\Column(name="per_lastname", type="string", length=100, nullable=true)
     */
    private $perLastname;

    /**
     * @var boolean
     *
     * @ORM\Column(name="per_active", type="boolean", nullable=false)
     */
    private $perActive;

    /**
     * @var string
     *
     * @ORM\Column(name="per_telephone", type="string", length=100, nullable=true)
     */
    private $perTelephone;

    /**
     * @var string
     *
     * @ORM\Column(name="per_sex", type="string", nullable=false)
     */
    private $perSex;

    /**
     * @var string
     *
     * @ORM\Column(name="per_language", type="string", length=2, nullable=true)
     */
    private $perLanguage;

    /**
     * @var string
     *
     * @ORM\Column(name="per_function", type="string", length=300, nullable=true)
     */
    private $perFunction;

    /**
     * @var string
     *
     * @ORM\Column(name="per_mobile", type="string", length=100, nullable=true)
     */
    private $perMobile;

    /**
     * @var string
     *
     * @ORM\Column(name="per_country_workplace", type="string", length=3, nullable=false)
     */
    private $perCountryWorkplace;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="per_date_contract_start", type="date", nullable=true)
     */
    private $perDateContractStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="per_date_contract_stop", type="date", nullable=true)
     */
    private $perDateContractStop;

    /**
     * @var string
     *
     * @ORM\Column(name="job_class", type="string", nullable=false)
     */
    private $jobClass;

    /*
     * @var string
     */
    private $generatedPassword;

    public function getId()
    {
        return $this->perId;
    }

    public function setId($perId)
    {
        $this->perId = $perId;
        return $this;
    }

    public function getEmail()
    {
        return $this->perEmail;
    }

    public function setEmail($perEmail)
    {
        $this->perEmail = $perEmail;
        return $this;
    }

    public function getFirstname()
    {
        return $this->perFirstname;
    }

    public function setFirstname($perFirstname)
    {
        $this->perFirstname = $perFirstname;
        return $this;
    }

    public function getLastname()
    {
        return $this->perLastname;
    }

    public function setLastname($perLastname)
    {
        $this->perLastname = $perLastname;
        return $this;
    }

    public function isPerActive()
    {
        return $this->perActive;
    }

    public function setActive($perActive)
    {
        $this->perActive = $perActive;
        return $this;
    }

    public function getTelephone()
    {
        return $this->perTelephone;
    }

    public function setTelephone($perTelephone)
    {
        $this->perTelephone = $perTelephone;
        return $this;
    }

    public function getSex()
    {
        return $this->perSex;
    }

    public function setSex($perSex)
    {
        $this->perSex = $perSex;
        return $this;
    }

    public function getLanguage()
    {
        return $this->perLanguage;
    }

    public function setLanguage($perLanguage)
    {
        $this->perLanguage = $perLanguage;
        return $this;
    }

    public function getFunction()
    {
        return $this->perFunction;
    }

    public function setFunction($perFunction)
    {
        $this->perFunction = $perFunction;
        return $this;
    }

    public function getMobile()
    {
        return $this->perMobile;
    }

    public function setMobile($perMobile)
    {
        $this->perMobile = $perMobile;
        return $this;
    }

    public function getCountryWorkplace()
    {
        return $this->perCountryWorkplace;
    }

    public function setCountryWorkplace($perCountryWorkplace)
    {
        $this->perCountryWorkplace = $perCountryWorkplace;
        return $this;
    }

    public function getDateContractStart()
    {
        return $this->perDateContractStart;
    }

    public function setDateContractStart($perDateContractStart)
    {
        $this->perDateContractStart = $perDateContractStart;
        return $this;
    }

    public function getDateContractStop()
    {
        return $this->perDateContractStop;
    }

    public function setDateContractStop($perDateContractStop)
    {
        $this->perDateContractStop = $perDateContractStop;
        return $this;
    }

    public function getJobClass()
    {
        return $this->jobClass;
    }

    public function setJobClass($jobClass)
    {
        $this->jobClass = $jobClass;
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

    public function getFullName()
    {
        return $this->getFirstname() . ' ' . strtoupper($this->getLastname());
    }

    public function getLogin()
    {
        if ($this->getUsername() !== false && strpos($this->getUsername(), '.') !== false) {
            $username = explode('.', $this->getUsername());
            $login = $username[0][0] . substr($username[1], 0, 7) . $this->getId();
            return strtolower($login);
        }
        return false;
    }

    public function getDomainAccount()
    {
        if ($this->getUsername() !== false) {
            return $this->getUsername() . self::MAIN_DOMAIN;
        }
        return false;
    }

    public function getUsername()
    {
        if (!empty($this->getEmail()) && strpos($this->getEmail(), '@') !== false) {
            return substr($this->getEmail(), 0, strpos($this->getEmail(), '@'));
        }
        return false;
    }

    public function getDisplayName()
    {
        return $this->getFirstname() . ', ' . strtoupper($this->getLastname());
    }
}
