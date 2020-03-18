<?php

namespace BisBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class PhoneDirectory
 *
 * @package BisBundle\Service
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class Staff
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->repository = $em->getRepository('BisBundle:BisPhone');
    }

    public function getByCountry(string $country):  ? array
    {
        return $this->repository->getStaffByCountry($country);
    }

    public function getAll()
    {
        return $this->repository->getStaff();
    }

    public function getField()
    {
        return $this->repository->getFieldStaff();
    }

    public function getHQ()
    {
        return $this->repository->getHQStaff();
    }

    public function getResRep()
    {
        return $this->repository->getResRepStaff();
    }
}
