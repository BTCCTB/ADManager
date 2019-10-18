<?php

namespace BisBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class BisCountry
 *
 * @package BisBundle\Entity
 *
 * @ORM\Entity(repositoryClass="BisBundle\Repository\BisCountryRepository")
 * @ORM\Table(name="bis_country")
 *
 * @UniqueEntity(fields={"private $cou_isocode_int"})
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class BisCountry
{

    /**
     * @var int
     *
     * @ORM\Column(name="cou_id", type="bigint")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $couId;

    /**
     * @var int
     *
     * @ORM\Column(name="cou_isocode_int", type="integer")
     */
    private $couIsocodeInt;

    // @codingStandardsIgnoreStart
    /**
     * @var string
     * @ORM\Column(name="cou_isocode_2letters", type="string")
     */
    private $couIsocode2letters;

    /**
     * @ORM\Column(name="cou_isocode_3letters", type="string", unique=true)
     * @ORM\Id
     */
    private $couIsocode3letters;
    // @codingStandardsIgnoreEnd
    /**
     * @ORM\Column(name="cou_name", type="string")
     */
    private $couName;

    /**
     * @ORM\Column(name="cou_navisioncode", type="string")
     */
    private $couNavisioncode;

    /**
     * @ORM\Column(name="cou_lastupdated", type="datetime")
     */
    private $couLastupdated;

    /**
     * @ORM\Column(name="cou_isiban", type="integer")
     */
    private $couIsiban;

    /**
     * @ORM\Column(name="cou_maskiban", type="string")
     */
    private $couMaskiban;

    /**
     * @ORM\Column(name="cou_isbic", type="integer")
     */
    private $couIsbic;

    /**
     * @ORM\Column(name="cou_mask", type="string")
     */
    private $couMask;

    /**
     * @ORM\Column(name="cou_devise", type="integer")
     */
    private $couDevise;

    /**
     * @ORM\Column(name="cou_maskpostalcode", type="string")
     */
    private $couMaskpostalcode;

    /**
     * @ORM\Column(name="cou_capital", type="string")
     */
    private $couCapital;

    /**
     * @ORM\Column(name="cou_latitude", type="float")
     */
    private $couLatitude;

    /**
     * @ORM\Column(name="cou_longitude", type="float")
     */
    private $couLongitude;

    /**
     * @ORM\Column(name="cou_calling_code", type="integer")
     */
    private $couCallingCode;

    /**
     * @ORM\Column(name="cou_region", type="string")
     */
    private $couRegion;

    /**
     * @ORM\Column(name="cou_subregion", type="string")
     */
    private $couSubregion;

    /**
     * @ORM\Column(name="cou_partner", type="boolean")
     */
    private $couPartner;

    /**
     * @ORM\OneToMany(targetEntity="BisBundle\Entity\BisPersonView", mappedBy="perCountryWorkplace")
     */
    private $bisPersons;

    /**
     * @ORM\OneToMany(targetEntity="BisBundle\Entity\BisPhone", mappedBy="countryWorkplace")
     */
    private $bisPhones;

    public function __construct()
    {
        $this->bisPersons = new ArrayCollection();
        $this->bisPhones = new ArrayCollection();
    }

    public function getCouId()
    {
        return $this->couId;
    }

    public function setCouId($couId)
    {
        $this->couId = $couId;

        return $this;
    }

    public function getCouIsocodeInt()
    {
        return $this->couIsocodeInt;
    }

    public function setCouIsocodeInt($couIsocodeInt)
    {
        $this->couIsocodeInt = $couIsocodeInt;

        return $this;
    }

    public function getCouIsocode2letters()
    {
        return $this->couIsocode2letters;
    }

    public function setCouIsocode2letters($couIsocode2letters)
    {
        $this->couIsocode2letters = $couIsocode2letters;

        return $this;
    }

    public function getCouIsocode3letters()
    {
        return $this->couIsocode3letters;
    }

    public function setCouIsocode3letters($couIsocode3letters)
    {
        $this->couIsocode3letters = $couIsocode3letters;

        return $this;
    }

    public function getCouName()
    {
        return $this->couName;
    }

    public function setCouName($couName)
    {
        $this->couName = $couName;

        return $this;
    }

    public function getCouNavisioncode()
    {
        return $this->couNavisioncode;
    }

    public function setCouNavisioncode($couNavisioncode)
    {
        $this->couNavisioncode = $couNavisioncode;

        return $this;
    }

    public function getCouLastupdated()
    {
        return $this->couLastupdated;
    }

    public function setCouLastupdated($couLastupdated)
    {
        $this->couLastupdated = $couLastupdated;

        return $this;
    }

    public function getCouIsiban()
    {
        return $this->couIsiban;
    }

    public function setCouIsiban($couIsiban)
    {
        $this->couIsiban = $couIsiban;

        return $this;
    }

    public function getCouMaskiban()
    {
        return $this->couMaskiban;
    }

    public function setCouMaskiban($couMaskiban)
    {
        $this->couMaskiban = $couMaskiban;

        return $this;
    }

    public function getCouIsbic()
    {
        return $this->couIsbic;
    }

    public function setCouIsbic($couIsbic)
    {
        $this->couIsbic = $couIsbic;

        return $this;
    }

    public function getCouMask()
    {
        return $this->couMask;
    }

    public function setCouMask($couMask)
    {
        $this->couMask = $couMask;

        return $this;
    }

    public function getCouDevise()
    {
        return $this->couDevise;
    }

    public function setCouDevise($couDevise)
    {
        $this->couDevise = $couDevise;

        return $this;
    }

    public function getCouMaskpostalcode()
    {
        return $this->couMaskpostalcode;
    }

    public function setCouMaskpostalcode($couMaskpostalcode)
    {
        $this->couMaskpostalcode = $couMaskpostalcode;

        return $this;
    }

    public function getCouCapital()
    {
        return $this->couCapital;
    }

    public function setCouCapital($couCapital)
    {
        $this->couCapital = $couCapital;

        return $this;
    }

    public function getCouLatitude()
    {
        return $this->couLatitude;
    }

    public function setCouLatitude($couLatitude)
    {
        $this->couLatitude = $couLatitude;

        return $this;
    }

    public function getCouLongitude()
    {
        return $this->couLongitude;
    }

    public function setCouLongitude($couLongitude)
    {
        $this->couLongitude = $couLongitude;

        return $this;
    }

    public function getCouCallingCode()
    {
        return $this->couCallingCode;
    }

    public function setCouCallingCode($couCallingCode)
    {
        $this->couCallingCode = $couCallingCode;

        return $this;
    }

    public function getCouRegion()
    {
        return $this->couRegion;
    }

    public function setCouRegion($couRegion)
    {
        $this->couRegion = $couRegion;

        return $this;
    }

    public function getCouSubregion()
    {
        return $this->couSubregion;
    }

    public function setCouSubregion($couSubregion)
    {
        $this->couSubregion = $couSubregion;

        return $this;
    }

    public function getCouPartner()
    {
        return $this->couPartner;
    }

    public function setCouPartner($couPartner)
    {
        $this->couPartner = $couPartner;

        return $this;
    }

    public function __toString()
    {
        return $this->getCouName();
    }
}
