<?php

namespace App\Repository;

use App\Entity\EnabelUserSms;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EnabelUserSms|null find($id, $lockMode = null, $lockVersion = null)
 * @method EnabelUserSms|null findOneBy(array $criteria, array $orderBy = null)
 * @method EnabelUserSms[]    findAll()
 * @method EnabelUserSms[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnabelUserSmsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnabelUserSms::class);
    }

    // /**
    //  * @return EnabelUserSms[] Returns an array of EnabelUserSms objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EnabelUserSms
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
