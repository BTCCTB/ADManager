<?php

namespace BisBundle\Entity;

use App\Entity\Account;
use AuthBundle\Service\ActiveDirectoryHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class BisPersonView
 *
 * @package BisBundle\Entity
 *
 * @ORM\Entity(repositoryClass="BisBundle\Repository\BisPersonViewRepository")
 * @ORM\Table(name="bis_person_view")
 *
 * @UniqueEntity(fields={"per_email"})
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class BisPersonView
{
    const MAIN_DOMAIN = '@enabel.be';
    const COMPANY = 'Enabel';

    /**
     * @var int
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
     * @var string
     *
     * @ORM\Column(name="per_nickname", type="string", length=100, nullable=true)
     */
    private $perNickname;

    /**
     * @var bool
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
     * @var BisCountry
     *
     * @ORM\ManyToOne(targetEntity="BisBundle\Entity\BisCountry", inversedBy="bisPersons")
     * @ORM\JoinColumn(name="per_country_workplace", referencedColumnName="cou_isocode_3letters", nullable=true)
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
        return Account::cleanUpEmail($this->perEmail);
    }

    public function setEmail($perEmail)
    {
        $this->perEmail = Account::cleanUpEmail($perEmail);

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

    public function getInitials()
    {
        if (!empty($this->getSex())) {
            return $this->getSex();
        }

        return null;
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

    public function getDepartment()
    {
        if (!empty($this->getCountryWorkplace()) && $this->getCountryWorkplace() instanceof BisCountry) {
            return $this->getCountryWorkplace()->getCouName();
        }

        return null;
    }

    public function getCountry()
    {
        if (!empty($this->getCountryWorkplace()) && $this->getCountryWorkplace() instanceof BisCountry) {
            return $this->getCountryWorkplace()->getCouIsocode3letters();
        }

        return null;
    }

    public function getCountryFlag()
    {
        if (!empty($this->getCountryWorkplace()) && $this->getCountryWorkplace() instanceof BisCountry) {
            return "<i class=\"flag-icon flag-icon-" . strtolower($this->getCountryWorkplace()->getCouIsocode2letters()) . "\" " .
            "title=\"" . $this->getCountryWorkplace()->getCouName() . "\" " .
            "alt=\"" . $this->getCountryWorkplace()->getCouName() . "\"></i>";
        }

        return null;
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

    public function getAccountName()
    {
        return $this->getLogin();
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
        if (!empty($this->getNickname())) {
            return strtoupper($this->getLastname()) . ', ' . ucfirst(strtolower($this->getNickname()));
        }

        return strtoupper($this->getLastname()) . ', ' . ucfirst(strtolower($this->getFirstname()));
    }

    public function getCommonName()
    {
        return ucfirst(strtolower($this->getFirstname())) . ' ' . strtoupper($this->getLastname());
    }

    public function getPrettyDateContractStop()
    {
        if (!empty($this->getDateContractStop())) {
            return $this->getDateContractStop()->format("Y-m-d");
        }

        return null;
    }

    public function getPrettyDateContractStart()
    {
        if (!empty($this->getDateContractStart())) {
            return $this->getDateContractStart()->format("Y-m-d");
        }

        return null;
    }

    public function getInfo()
    {
        $info = [];
        if (null !== $this->getPrettyDateContractStart()) {
            $info['startDate'] = $this->getPrettyDateContractStart();
        }
        if (null !== $this->getPrettyDateContractStop()) {
            $info['endDate'] = $this->getPrettyDateContractStop();
        }
        if (!empty($info)) {
            return json_encode($info);
        }

        return null;
    }

    public function getCompany()
    {
        return self::COMPANY;
    }

    public function getFirstAttribute($key)
    {
        return $this->getAttribute($key);
    }

    public function getAttribute($key)
    {
        switch ($key) {
            case 'per_id':
                return $this->getId();
            case 'per_firstname':
                return $this->getFirstname();
            case 'per_lastname':
                return $this->getLastname();
            case 'per_email':
                return $this->getEmail();
            case 'displayname':
                return $this->getDisplayName();
            case 'per_sex':
                return $this->getSex();
            case 'per_function':
                return $this->getFunction();
            case 'c':
                if (!empty($this->getCountryWorkplace()) && $this->getCountryWorkplace() instanceof BisCountry) {
                    return $this->getCountryWorkplace()->getCouIsocode2letters();
                }

                return null;
            case 'co':
                if (!empty($this->getCountryWorkplace()) && $this->getCountryWorkplace() instanceof BisCountry) {
                    return $this->getCountryWorkplace()->getCouName();
                }

                return null;

            case 'preferredLanguage':
                return $this->getPreferredLanguage();
        }

        return null;
    }

    public function getTitle()
    {
        if (!empty($this->getFunction())) {
            return mb_substr($this->getFunction(), 0, 60);
        }

        return null;
    }

    public function getDescription()
    {
        if (!empty($this->getFunction())) {
            return mb_substr($this->getFunction(), 0, 60);
        }

        return null;
    }

    public function getProxyAddresses()
    {
        return [
            'SMTP:' . $this->getUsername() . self::MAIN_DOMAIN,
//            'smtp:' . $this->getBusinessCategory(),
        ];
    }

    public function getUserPrincipalName()
    {
        return $this->getDomainAccount();
    }

    /**
     * @return string|null
     */
    public function getOrganizationalUnit()
    {
        if (!empty($this->getCountryWorkplace()) && $this->getCountryWorkplace() instanceof BisCountry) {
            return ActiveDirectoryHelper::createCountryDistinguishedName($this->getCountryWorkplace()->getCouIsocode3letters());
        }

        return null;
    }

    public function getEmployeeId()
    {
        return $this->getId();
    }

    public function getPreferredLanguage()
    {
        return $this->getLanguage();
    }

    public function getBusinessCategory()
    {
        //return str_replace('@enabel.be', '@btcctb.org', $this->getEmail());
    }

    public function getDivision()
    {
        return $this->getJobClass();
    }

    public function getNickname()
    {
        return $this->perNickname;
    }

    public function setNickname($perNickname)
    {
        $this->perNickname = $perNickname;

        return $this;
    }
}
