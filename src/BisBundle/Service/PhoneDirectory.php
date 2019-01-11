<?php

namespace BisBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class PhoneDirectory
 *
 * @package BisBundle\Service
 * @author  Damien Lagae <damienlagae@gmail.com>
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
}
