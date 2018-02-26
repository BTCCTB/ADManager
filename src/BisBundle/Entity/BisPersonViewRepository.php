<?php

namespace BisBundle\Entity;

use Adldap\Models\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class BisPersonViewRepository
 *
 * @package BisBundle\Entity
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class BisPersonViewRepository extends EntityRepository
{
    private function getMemberOtTheBoard()
    {
        return [
            50180,
            50413,
            50175,
            50176,
            50177,
            50435,
            50183,
            50470,
            50472,
            50473,
            50227,
            50186,
            50187,
        ];
    }

    public function getUserByUsername($username)
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perEmail LIKE :email')
            ->setParameter('email', $username . '@%')
            ->getQuery();

        try {
            return $query->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * Get a user by Email
     *
     * @param string $email The email of the user
     *
     * @return BisPersonView|null The User or null if not found
     */
    public function getUserByEmail(string $email)
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $email2 = $email;

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perEmail LIKE :email or bpv.perEmail LIKE :email2')
            ->setParameter('email', $email);

        // Test enabel.be
        if (strpos($email2, '@enabel.be')) {
            $email2 = str_replace('@enabel.be', '@btcctb.org', $email2);
        } elseif (strpos($email2, '@btcctb.org')) {
            $email2 = str_replace('@btcctb.org', '@enabel.be', $email2);
        }
        $query->setParameter('email2', $email2);

        try {
            return $query->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findAllFieldUser()
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perCountryWorkplace != :country')
            ->andWhere("bpv.perCountryWorkplace != ''")
            ->setParameter('country', 'BEL')
            ->getQuery();

        return $query->getResult();
    }

    public function findAllHqUser()
    {
        return $this->findAllUserByCountryWorkplace('BEL');
    }

    public function findAllUserByCountryWorkplace($countryWorkplace)
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perCountryWorkplace = :country')
            ->andWhere('bpv.perId NOT IN (:perIds)')
            ->setParameter('country', $countryWorkplace)
            ->setParameter('perIds', $this->getMemberOtTheBoard())
            ->getQuery();

        return $query->getResult();
    }

    public function findAll()
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perId NOT IN (:perIds)')
            ->setParameter('perIds', $this->getMemberOtTheBoard())
            ->getQuery();

        return $query->getResult();
    }
}
