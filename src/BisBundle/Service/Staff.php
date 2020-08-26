<?php

namespace BisBundle\Service;

use BisBundle\Entity\BisPhone;
use BisBundle\Repository\BisPhoneRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class PhoneDirectory
 *
 * @package BisBundle\Service
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class Staff
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->repository = $em->getRepository(BisPhone::class);
    }

    public function getByCountry(string $country):  ? array
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->repository;
        return $bisPhoneRepo->getStaffByCountry($country);
    }

    public function getAll()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->repository;
        return $bisPhoneRepo->getStaff();
    }

    public function getField()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->repository;
        return $bisPhoneRepo->getFieldStaff();
    }

    public function getHQ()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->repository;
        return $bisPhoneRepo->getHQStaff();
    }

    public function getResRep()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->repository;
        return $bisPhoneRepo->getResRepStaff();
    }
}
