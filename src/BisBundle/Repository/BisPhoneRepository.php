<?php

namespace BisBundle\Repository;

use BisBundle\Entity\BisPhone;
use Doctrine\ORM\EntityRepository;

/**
 * Class BisPhoneRepository
 *
 * @package BisBundle\Entity
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class BisPhoneRepository extends EntityRepository
{
    /**
     * Get phone directory by country
     *
     * @param string $country The country code [iso3letter] of the directory
     *
     * @return BisPhone[]|null The directory or null if not found
     */
    public function getPhoneDirectoryByCountry(string $country):  ? array
    {
        $repository = $this->_em->getRepository(BisPhone::class);

        $query = $repository->createQueryBuilder('bp')
            ->where('bp.countryWorkplace = :country')
            ->andWhere('bp.email IS NOT NULL')
            ->setParameter('country', $country);

        return $query->getQuery()->getResult();
    }

    /**
     * Get phone directory for field
     *
     * @return BisPhone[]|null The directory or null if not found
     */
    public function getFieldPhoneDirectory() :  ? array
    {
        $repository = $this->_em->getRepository(BisPhone::class);

        $query = $repository->createQueryBuilder('bp')
            ->where('bp.countryWorkplace != :country')
            ->andWhere("bp.countryWorkplace != ''")
            ->setParameter('country', 'BEL')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get HQ phone directory
     *
     * @return BisPhone[]|null The directory or null if not found
     */
    public function getHQPhoneDirectory() :  ? array
    {
        return $this->getPhoneDirectoryByCountry('BEL');
    }

    /**
     * Get full phone directory
     *
     * @return BisPhone[]|null The directory or null if not found
     */
    public function getPhoneDirectory() :  ? array
    {
        $repository = $this->_em->getRepository(BisPhone::class);

        $query = $repository->createQueryBuilder('bp')
            ->where('bp.Id NOT IN (:ids)')
            ->setParameter('ids', BisPersonViewRepository::getMemberOtTheBoard())
            ->getQuery();

        return $query->getResult();
    }
}
