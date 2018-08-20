<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * Class UserRepository
 *
 * @package App\Repository
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class UserRepository extends EntityRepository
{
    /**
     * @param String $accountName
     * @param String $newEmail
     *
     * @return null|User
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function changeEmail(String $accountName, String $newEmail)
    {
        $user = $this->findOneBy(['accountName' => $accountName]);

        if ($user !== null) {
            $user->setEmail($newEmail);
            $this->_em->persist($user);
            $this->_em->flush();
        }

        return $user;
    }
}
