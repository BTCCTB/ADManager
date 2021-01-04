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
    private $bis;

    public function __construct(EntityManager $bis)
    {
        $this->bis = $bis->getRepository(BisPhone::class);
    }

    public function getByCountry(string $country):  ? array
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getStaffByCountry($country);
    }

    public function getAll()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getStaff();
    }

    public function getField()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getFieldStaff();
    }

    public function getHQ()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getHQStaff();
    }

    public function getResRep()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getResRepStaff();
    }
}
