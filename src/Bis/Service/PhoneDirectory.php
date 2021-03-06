<?php

namespace Bis\Service;

use Bis\Entity\BisPhone;
use Bis\Repository\BisPhoneRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class PhoneDirectory
 *
 * @package Bis\Service
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class PhoneDirectory
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
        return $bisPhoneRepo->getPhoneDirectoryByCountry($country);
    }

    public function getAll()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getPhoneDirectory();
    }

    public function getField()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getFieldPhoneDirectory();
    }

    public function getHQ()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getHQPhoneDirectory();
    }

    public function getResRep()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getResRepPhoneDirectory();
    }

    public function getIctHq()
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getIctHqPhoneDirectory();
    }

    public function getContactByIds(array $arrayId)
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getStaffByIds($arrayId);
    }

    public function getContactById(int $id)
    {
        /** @var BisPhoneRepository $bisPhoneRepo*/
        $bisPhoneRepo = $this->bis;
        return $bisPhoneRepo->getStaffById($id);
    }

    /**
     * @return array
     */
    public function getRecipientOptions()
    {
        $recipients = [];
        $persons = $this->getAll();
        foreach ($persons as $person) {
            if (!empty($person->getMobile())) {
                $recipients[$person->getDisplayName()] = $person->getId();
            }
        }

        return $recipients;
    }
}
