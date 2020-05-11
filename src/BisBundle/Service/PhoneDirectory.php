<?php

namespace BisBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class PhoneDirectory
 *
 * @package BisBundle\Service
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class PhoneDirectory
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
        return $this->repository->getPhoneDirectoryByCountry($country);
    }

    public function getAll()
    {
        return $this->repository->getPhoneDirectory();
    }

    public function getField()
    {
        return $this->repository->getFieldPhoneDirectory();
    }

    public function getHQ()
    {
        return $this->repository->getHQPhoneDirectory();
    }

    public function getResRep()
    {
        return $this->repository->getResRepPhoneDirectory();
    }

    public function getIctHq()
    {
        return $this->repository->getIctHqPhoneDirectory();
    }

    public function getContactByIds(array $arrayId)
    {
        return $this->repository->getStaffByIds($arrayId);
    }

    public function getContactById(int $id)
    {
        return $this->repository->getStaffById($id);
    }

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
