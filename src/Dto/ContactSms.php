<?php

namespace App\Dto;

use BisBundle\Entity\BisPersonView;
use BisBundle\Entity\BisPhone;

/**
 * DTO to represent a ContactSms based on BisPersonView Entity
 */
class ContactSms
{
    private $id;
    private $firstname;
    private $lastname;
    private $email;
    private $phone;
    private $country;
    private $language;
    private $gender;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

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

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Load contact information from BisPersonView entity
     *
     * @param BisPhone $bisPhone The contact in BisPersonView format
     *
     * @return ContactSms The contact
     */
    public static function loadFromBisPhone(BisPhone $bisPhone): ContactSms
    {
        $contact = new self();
        $contact
            ->setId($bisPhone->getId())
            ->setFirstname($bisPhone->getFirstname())
            ->setLastname($bisPhone->getLastname())
            ->setEmail($bisPhone->getEmail())
            ->setPhone($bisPhone->getMobile())
            ->setCountry($bisPhone->getCountry())
            ->setLanguage($bisPhone->getLanguage())
            ->setGender($bisPhone->getSex())
        ;

        return $contact;
    }
}
