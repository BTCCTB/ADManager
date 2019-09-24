<?php

namespace BisBundle\Entity;

use App\Entity\Account;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BisPersonSf
 *
 * @package BisBundle\Entity
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 *
 * @ORM\Entity(repositoryClass="BisBundle\Repository\BisPersonSfRepository")
 * @ORM\Table(name="bis_person_sf")
 */
class BisPersonSf
{
    /**
     * @ORM\Column(type="integer", name="per_id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $perId;

    /**
     * @ORM\Column(type="string", name="per_email", length=100, nullable=true)
     */
    private $perEmail;

    /**
     * @ORM\Column(type="string", name="per_personal_email", length=100, nullable=true)
     */
    private $perPersonalEmail;

    /**
     * @ORM\Column(type="string", name="per_firstname", length=100)
     */
    private $perFirstname;

    /**
     * @ORM\Column(type="string", name="per_lastname", length=100)
     */
    private $perLastname;

    /**
     * @ORM\Column(type="string", name="per_nickname", length=100)
     */
    private $perNickname;

    /**
     * @ORM\Column(type="boolean", name="per_active")
     */
    private $perActive;

    /**
     * @ORM\Column(type="datetime", name="per_last_updated", options={"default": 0})
     */
    private $perLastUpdated;

    /**
     * @ORM\Column(type="string", name="per_telephone", length=100, nullable=true)
     */
    private $perTelephone;

    /**
     * @ORM\Column(type="string", name="per_skype", length=100, nullable=true)
     */
    private $perSkype;

    /**
     * @ORM\Column(type="string", name="per_gender")
     */
    private $perGender;

    /**
     * @ORM\Column(type="string", name="per_mobile", length=100, nullable=true)
     */
    private $perMobile;

    /**
     * @ORM\Column(type="string", name="per_mother_tongue", length=2)
     */
    private $perMotherTongue;

    /**
     * @ORM\Column(type="string", name="per_usual_lang", length=2)
     */
    private $perUsualLang;

    /**
     * @ORM\Column(type="text", name="per_image", nullable=true)
     */
    private $perImage;

    public function getPerId()
    {
        return $this->perId;
    }

    public function setPerId($perId)
    {
        $this->perId = $perId;

        return $this;
    }

    public function getPerEmail()
    {
        return Account::cleanUpEmail($this->perEmail);
    }

    public function setPerEmail($perEmail)
    {
        $this->perEmail = Account::cleanUpEmail($perEmail);

        return $this;
    }

    public function getPerPersonalEmail()
    {
        return Account::cleanUpEmail($this->perPersonalEmail);
    }

    public function setPerPersonalEmail($perPersonalEmail)
    {
        $this->perPersonalEmail = Account::cleanUpEmail($perPersonalEmail);

        return $this;
    }

    public function getPerFirstname()
    {
        return $this->perFirstname;
    }

    public function setPerFirstname($perFirstname)
    {
        $this->perFirstname = $perFirstname;

        return $this;
    }

    public function getPerLastname()
    {
        return $this->perLastname;
    }

    public function setPerLastname($perLastname)
    {
        $this->perLastname = $perLastname;

        return $this;
    }

    public function getPerNickname()
    {
        return $this->perNickname;
    }

    public function setPerNickname($perNickname)
    {
        $this->perNickname = $perNickname;

        return $this;
    }

    public function getPerActive()
    {
        return $this->perActive;
    }

    public function setPerActive($perActive)
    {
        $this->perActive = $perActive;

        return $this;
    }

    public function getPerLastUpdated()
    {
        return $this->perLastUpdated;
    }

    public function setPerLastUpdated($perLastUpdated)
    {
        $this->perLastUpdated = $perLastUpdated;

        return $this;
    }

    public function getPerTelephone()
    {
        return $this->perTelephone;
    }

    public function setPerTelephone($perTelephone)
    {
        $this->perTelephone = $perTelephone;

        return $this;
    }

    public function getPerSkype()
    {
        return $this->perSkype;
    }

    public function setPerSkype($perSkype)
    {
        $this->perSkype = $perSkype;

        return $this;
    }

    public function getPerGender()
    {
        return $this->perGender;
    }

    public function setPerGender($perGender)
    {
        $this->perGender = $perGender;

        return $this;
    }

    public function getPerMobile()
    {
        return $this->perMobile;
    }

    public function setPerMobile($perMobile)
    {
        $this->perMobile = $perMobile;

        return $this;
    }

    public function getPerMotherTongue()
    {
        return $this->perMotherTongue;
    }

    public function setPerMotherTongue($perMotherTongue)
    {
        $this->perMotherTongue = $perMotherTongue;

        return $this;
    }

    public function getPerUsualLang()
    {
        return $this->perUsualLang;
    }

    public function setPerUsualLang($perUsualLang)
    {
        $this->perUsualLang = $perUsualLang;

        return $this;
    }

    public function getPerImage()
    {
        return $this->perImage;
    }

    public function setPerImage($perImage)
    {
        $this->perImage = $perImage;

        return $this;
    }
}
