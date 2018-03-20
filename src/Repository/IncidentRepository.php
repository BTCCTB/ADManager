<?php

namespace App\Repository;

use App\Entity\Incident;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Incident|null find($id, $lockMode = null, $lockVersion = null)
 * @method Incident|null findOneBy(array $criteria, array $orderBy = null)
 * @method Incident[]    findAll()
 * @method Incident[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncidentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Incident::class);
    }

    public function getStats()
    {
        return $this->createQueryBuilder('i')
            ->select('count(i.id) as active')
            ->where('i.endDate <= :now')
            ->orWhere('i.endDate IS NULL')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findActive()
    {
        return $this->createQueryBuilder('i')
            ->where('i.endDate <= :now')
            ->orWhere('i.endDate IS NULL')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult()
        ;
    }

    /*
public function findBySomething($value)
{
return $this->createQueryBuilder('i')
->where('i.something = :value')->setParameter('value', $value)
->orderBy('i.id', 'ASC')
->setMaxResults(10)
->getQuery()
->getResult()
;
}
 */
}
