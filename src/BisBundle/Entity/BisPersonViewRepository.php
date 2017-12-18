<?php

namespace BisBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class BisPersonViewRepository
 *
 * @package BisBundle\Entity
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class BisPersonViewRepository extends EntityRepository
{
    public function getUserByUsername($username)
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perEmail LIKE :email')
            ->setParameter('email', $username . '@%')
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getUserByEmail($email)
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perEmail LIKE :email')
            ->setParameter('email', $email)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findAllFieldUser()
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perCountryWorkplace != :country')
            ->setParameter('country', 'BEL')
            ->getQuery();

        return $query->getResult();
    }

    public function findAllHqUser()
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perCountryWorkplace = :country')
            ->setParameter('country', 'BEL')
            ->getQuery();

        return $query->getResult();
    }

    public function findAllUserByCountryWorkplace($countryWorkplace)
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perCountryWorkplace = :country')
            ->setParameter('country', $countryWorkplace)
            ->getQuery();

        return $query->getResult();
    }
}
