<?php

namespace BisBundle\Repository;

use BisBundle\Entity\BisPhone;
use Doctrine\ORM\EntityRepository;

/**
 * Class BisPhoneRepository
 *
 * @package BisBundle\Entity
 *
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
        return $this->getBaseQueryWithPhone()
            ->andWhere('bp.countryWorkplace = :country')
            ->setParameter('country', $country)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get full phone directory
     *
     * @return BisPhone[]|null The directory or null if not found
     */
    public function getPhoneDirectory() :  ? array
    {
        return $this->getBaseQueryWithPhone()
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
        return $this->getBaseQueryWithPhone()
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

    public function getResRepPhoneDirectory() :  ? array
    {
        return $this->getBaseQueryWithPhone()
            ->andWhere('bp.function LIKE :ResRepNl OR bp.function LIKE :ResRepFr OR bp.function LIKE :ResRepEn')
            ->setParameter('ResRepFr', 'Représentant résident%')
            ->setParameter('ResRepNl', 'Plaatselijk vertegenwoordiger%')
            ->setParameter('ResRepEn', 'Resident Representative%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get full staff
     *
     * @return BisPhone[]|null The staff or null if not found
     */
    public function getStaff() :  ? array
    {
        return $this->getBaseQuery()
            ->getQuery()
            ->getResult();
    }

    /**
     * Get HQ Staff
     *
     * @return BisPhone[]|null The staff or null if not found
     */
    public function getHQStaff() :  ? array
    {
        return $this->getStaffByCountry('BEL');
    }

    public function getResRepStaff() :  ? array
    {
        return $this->getBaseQuery()
            ->andWhere('bp.function LIKE :ResRepNl OR bp.function LIKE :ResRepFr OR bp.function LIKE :ResRepEn')
            ->setParameter('ResRepFr', 'Représentant résident%')
            ->setParameter('ResRepNl', 'Plaatselijk vertegenwoordiger%')
            ->setParameter('ResRepEn', 'Resident Representative%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get staff for field
     *
     * @return BisPhone[]|null The staff or null if not found
     */
    public function getFieldStaff() :  ? array
    {
        return $this->getBaseQuery()
            ->andWhere('bp.countryWorkplace != :country')
            ->setParameter('country', 'BEL')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get staff list by country
     *
     * @param string $country The country code [iso3letter] of the staff
     *
     * @return BisPhone[]|null The staff or null if not found
     */
    public function getStaffByCountry(string $country) :  ? array
    {
        return $this->getBaseQuery()
            ->andWhere('bp.countryWorkplace = :country')
            ->setParameter('country', $country)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBaseQueryWithPhone()
    {
        return $this->getBaseQuery()
            ->andWhere('bp.mobile IS NOT NULL OR bp.telephone IS NOT NULL')
            ->andWhere('bp.mobile <> :empty OR bp.telephone <> :empty')
            ->setParameter('empty', '');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBaseQuery()
    {
        $repository = $this->_em->getRepository(BisPhone::class);

        return $repository->createQueryBuilder('bp')
            ->where("bp.countryWorkplace != ''")
            ->andWhere('bp.email IS NOT NULL')
            ->andWhere('bp.email <> :emptyMail')
            ->andWhere('bp.id NOT IN (:ids)')
            ->setParameter('ids', BisPersonViewRepository::getMemberOtTheBoard())
            ->setParameter('emptyMail', '')
            ->orderBy('bp.lastname', 'ASC')
            ->addOrderBy('bp.firstname', 'ASC');
    }
}
