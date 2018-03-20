<?php

namespace App\Repository;

use App\Entity\IncidentSeverity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method IncidentSeverity|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncidentSeverity|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncidentSeverity[]    findAll()
 * @method IncidentSeverity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncidentSeverityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, IncidentSeverity::class);
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
