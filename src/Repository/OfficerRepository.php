<?php

namespace App\Repository;

use App\Entity\Officer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Officer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Officer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Officer[]    findAll()
 * @method Officer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfficerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Officer::class);
    }

    /**
     * Generate query to search a officer
     *
     * @return \Doctrine\ORM\Query
     */
    public function search(String $searchCriteria = null)
    {
        return $this->createQueryBuilder('o')
            ->join('o.user', 'u')
            ->andWhere(
                '(u.firstname LIKE :criteria '.
                'OR u.lastname LIKE :criteria '.
                'OR u.email LIKE :criteria '.
                'OR o.countries LIKE :criteria)'
            )
            ->setParameter('criteria', (empty($searchCriteria)?'%':'%' . $searchCriteria . '%'))
            ->getQuery()
            ;
    }
}
