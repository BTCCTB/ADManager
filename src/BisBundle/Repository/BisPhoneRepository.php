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

    private function getBaseQuery()
    {
        $repository = $this->_em->getRepository(BisPhone::class);

        return $repository->createQueryBuilder('bp')
            ->where("bp.countryWorkplace != ''")
            ->andWhere('bp.email IS NOT NULL')
            ->andWhere('bp.email <> :empty')
            ->andWhere('bp.mobile IS NOT NULL OR bp.telephone IS NOT NULL')
            ->andWhere('bp.mobile <> :empty OR bp.telephone <> :empty')
            ->andWhere('bp.id NOT IN (:ids)')
            ->setParameter('ids', BisPersonViewRepository::getMemberOtTheBoard())
            ->setParameter('empty', '')
            ->orderBy('bp.lastname', 'ASC')
            ->addOrderBy('bp.firstname', 'ASC');
    }
    /**
     * Get phone directory by country
     *
     * @param string $country The country code [iso3letter] of the directory
     *
     * @return BisPhone[]|null The directory or null if not found
     */
    public function getPhoneDirectoryByCountry(string $country):  ? array
    {
        return $this->getBaseQuery()
            ->andWhere('bp.countryWorkplace = :country')
            ->setParameter('country', $country)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get phone directory for field
     *
     * @return BisPhone[]|null The directory or null if not found
     */
    public function getFieldPhoneDirectory() :  ? array
    {
        return $this->getBaseQuery()
            ->andWhere('bp.countryWorkplace != :country')
            ->setParameter('country', 'BEL')
            ->getQuery()
            ->getResult();
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
        return $this->getBaseQuery()
            ->getQuery()
            ->getResult();
    }
}
