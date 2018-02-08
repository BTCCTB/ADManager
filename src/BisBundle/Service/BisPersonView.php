<?php

namespace BisBundle\Service;

use BisBundle\Entity\BisPersonViewRepository;
use Doctrine\ORM\EntityManager;

/**
 * Class BisPersonView
 *
 * @package BisBundle\Service
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
}
