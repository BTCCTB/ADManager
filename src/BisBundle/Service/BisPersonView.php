<?php

namespace BisBundle\Service;

use BisBundle\Entity\BisPersonViewRepository;
use Doctrine\ORM\EntityManager;

/**
 * Class BisPersonView
 *
 * @package BisBundle\Service
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class BisPersonView
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var BisPersonViewRepository
     */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('BisBundle:BisPersonView');
    }

    /**
     * @param String $email
     *
     * @return \BisBundle\Entity\BisPersonView|null
     */
    public function getUser(String $email)
    {
        return $this->repository->getUserByEmail($email);
    }

    /**
     * @param String $email
     *
     * @return \BisBundle\Entity\BisPersonView|null
     */
    public function getUserData(String $email)
    {
        return $this->repository->getUserData($email);
    }

    /**
     * @param String $country Country code 3 letters iso
     *
     * @return \BisBundle\Entity\BisPersonView[]|null
     */
    public function getCountryUsers(String $country = null)
    {
        if (null !== $country) {
            return $this->repository->getUsersByCountry($country);
        }

        return $this->repository->findAllFieldUser();
    }

    public function getAllUsers()
    {
        return $this->repository->findAll();
    }

    public function getActiveUserByEmail()
    {
        return $this->repository->getActiveUserByEmail();
    }

    public function getUserMobileByEmail()
    {
        return $this->repository->getUserMobileByEmail();
    }

    public function getStarters(int $nbDays = 15)
    {
        return $this->repository->getStarters($nbDays);
    }

    public function getFinishers(int $nbDays = 15)
    {
        return $this->repository->getFinishers($nbDays);
    }
}
