<?php

namespace App\Repository;

use App\Entity\SecurityAudit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SecurityAudit|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecurityAudit|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecurityAudit[]    findAll()
 * @method SecurityAudit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecurityAuditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecurityAudit::class);
    }

    /*
public function findBySomething($value)
{
return $this->createQueryBuilder('s')
->where('s.something = :value')->setParameter('value', $value)
->orderBy('s.id', 'ASC')
->setMaxResults(10)
->getQuery()
->getResult()
;
}
 */
}
