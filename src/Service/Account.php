<?php

namespace App\Service;

use Adldap\Models\User;
use App\Repository\AccountRepository;
use BisBundle\Entity\BisPersonView;
use Doctrine\ORM\EntityManager;

/**
 * Class Account
 *
 * @package AuthBundle\Service
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class Account
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function updateCredentials(User $adAccount, $credentials)
    {
        $accountRepository = $this->em->getRepository(\App\Entity\Account::class);

        $account = $accountRepository->findOneBy([
            'email' => $adAccount->getEmail(),
        ]);

        if (empty($account)) {
            $account = new \App\Entity\Account();
            $account->setEmployeeId($adAccount->getEmployeeId())
                ->setAccountName($adAccount->getAccountName())
                ->setUserPrincipalName($adAccount->getUserPrincipalName())
                ->setEmail($adAccount->getEmail())
                ->setEmailContact($adAccount->getEmail())
                ->setFirstname($adAccount->getFirstName())
                ->setLastname($adAccount->getLastName())
                ->setToken($account->generateToken($account->getEmail(), $credentials))
                ->setActive(true);
        } else {
            $account->setEmployeeId($adAccount->getEmployeeId())
                ->setAccountName($adAccount->getAccountName())
                ->setUserPrincipalName($adAccount->getUserPrincipalName())
                ->setEmail($adAccount->getEmail())
                ->setEmailContact($adAccount->getEmail())
                ->setFirstname($adAccount->getFirstName())
                ->setLastname($adAccount->getLastName())
                ->setToken($account->generateToken($account->getEmail(), $credentials))
                ->setActive(true);
        }
        $this->em->persist($account);
        $this->em->flush();
    }

    public function getAccount(String $email)
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->em->getRepository(\App\Entity\Account::class);

        return $accountRepository->findByEmail($email);
    }

    public function lastLogin(String $email)
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->em->getRepository(\App\Entity\Account::class);
        $accountRepository->setLastLogin($email);
    }

    public function setGeneratedPassword(String $email, $password)
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->em->getRepository(\App\Entity\Account::class);
        $accountRepository->setGeneratedPassword($email, $password);
    }

    /**
     * Update or Insert a new account with bis_person_view info
     *
     * @param BisPersonView $bisPersonView
     *
     * @return integer 0: no action, 1: creation, 2: update
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function upSertAccount(BisPersonView $bisPersonView): int
    {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->em->getRepository(\App\Entity\Account::class);

        $account = $accountRepository->findOneBy([
            'employeeId' => $bisPersonView->getEmployeeId(),
        ]);
        $upsert = 0;

        if (!empty($bisPersonView->getEmail())) {
            if (empty($account)) {
                $account = new \App\Entity\Account();
                $account->setEmployeeId($bisPersonView->getEmployeeId())
                    ->setAccountName($bisPersonView->getAccountName())
                    ->setUserPrincipalName($bisPersonView->getUserPrincipalName())
                    ->setEmail($bisPersonView->getEmail())
                    ->setEmailContact($bisPersonView->getEmail())
                    ->setFirstname($bisPersonView->getFirstname())
                    ->setLastname($bisPersonView->getLastname())
                    ->setToken($account->generateToken($account->getEmail(), 'empty'))
                    ->setActive(true);
                $upsert = 1;
            } else {
                $account->setEmployeeId($bisPersonView->getEmployeeId())
                    ->setAccountName($bisPersonView->getAccountName())
                    ->setUserPrincipalName($bisPersonView->getUserPrincipalName())
                    ->setEmail($bisPersonView->getEmail())
                    ->setEmailContact($bisPersonView->getEmail())
                    ->setFirstname($bisPersonView->getFirstname())
                    ->setLastname($bisPersonView->getLastname());
                $upsert = 2;
            }
            $this->em->persist($account);
            $this->em->flush();
        }

        return $upsert;
    }
}
