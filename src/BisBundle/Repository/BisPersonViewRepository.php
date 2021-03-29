<?php

namespace BisBundle\Repository;

use Adldap\Models\User;
use AuthBundle\Service\ActiveDirectoryHelper;
use BisBundle\Entity\BisConjobSf;
use BisBundle\Entity\BisContractSf;
use BisBundle\Entity\BisCountry;
use BisBundle\Entity\BisJobSf;
use BisBundle\Entity\BisPersonSf;
use BisBundle\Entity\BisPersonView;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class BisPersonViewRepository
 *
 * @package BisBundle\Entity
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class BisPersonViewRepository extends EntityRepository
{
    public static function getMemberOtTheBoard()
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
            50799,
            50800,
            50809,
            50810,
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
     * Find a user by email in bis_person_sf
     *
     * @param string $email His email
     *
     * @return BisPersonView The user.
     */
    public function getUserData(string $email)
    {
        $bisPersonView = new BisPersonView();
        $bisPersonView->setEmail($email);

        $bisPersonRepository = $this->_em->getRepository(BisPersonSf::class);
        $bisContractRepository = $this->_em->getRepository(BisContractSf::class);
        $bisConJobRepository = $this->_em->getRepository(BisConjobSf::class);
        $bisJobRepository = $this->_em->getRepository(BisJobSf::class);
        $bisCountryRepository = $this->_em->getRepository(BisCountry::class);

        // Get person info
        $query = $bisPersonRepository->createQueryBuilder('bp')
            ->where('bp.perEmail LIKE :email')
            ->setParameter('email', $email)
            ->getQuery();

        $personData = $query->getOneOrNullResult();

        if (null !== $personData) {
            $bisPersonView
                ->setId($personData->getPerId())
                ->setFirstname($personData->getPerFirstname())
                ->setLastname($personData->getPerLastname())
                ->setNickname($personData->getPerNickname())
                ->setTelephone($personData->getPerTelephone())
                ->setSex($personData->getPerGender())
                ->setLanguage($personData->getPerUsualLang())
                ->setMobile($personData->getPerMobile())
            ;

            // Get contract Info
            $query = $bisContractRepository->createQueryBuilder('bcon')
                ->where('bcon.conPerId = :perId')
                ->setParameter('perId', $bisPersonView->getId())
                ->orderBy('bcon.conDateStart', 'DESC')
                ->getQuery();

            $contractDatas = $query->getResult();
            if (count($contractDatas) !== 0) {
                $contractData = $contractDatas[0];
                $bisPersonView
                    ->setDateContractStop($contractData->getConDateStop())
                    ->setDateContractStart($contractData->getConDateStart())
                    ->setActive($contractData->getConActive())
                ;

                $query = $bisConJobRepository->createQueryBuilder('bcj')
                    ->where('bcj.fkConId = :conId')
                    ->setParameter('conId', $contractData->getConId())
                    ->orderBy('bcj.fkJobId', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery();
                $conJobData = $query->getOneOrNullResult();

                if (null !== $conJobData) {
                    $query = $bisJobRepository->createQueryBuilder('bj')
                        ->where('bj.jobId = :jobId')
                        ->setParameter('jobId', $conJobData->getFkJobId())
                        ->getQuery();
                    $jobData = $query->getOneOrNullResult();

                    if (null !== $jobData) {
                        $bisPersonView
                            ->setFunction($jobData->getJobFunction())
                            ->setCountryWorkplace(
                                $bisCountryRepository->findOneBy(
                                    [
                                        'couIsocode3letters' => $jobData->getJobCountryWorkplace()
                                    ]
                                )
                            )
                            ->setJobClass($jobData->getJobClass())
                        ;
                    }
                }
            }
        }

        return $bisPersonView;
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

    /**
     * Get users by country
     *
     * @param string $country The country code [iso3letter] of the user
     *
     * @return BisPersonView[]|null The Users or null if not found
     */
    public function getUsersByCountry(string $country)
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perCountryWorkplace = :country')
            ->andWhere('bpv.perEmail IS NOT NULL')
            ->setParameter('country', $country);

        return $query->getQuery()->getResult();
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
            ->setParameter('perIds', self::getMemberOtTheBoard())
            ->getQuery();

        return $query->getResult();
    }

    public function findAll()
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perId NOT IN (:perIds)')
            ->setParameter('perIds', self::getMemberOtTheBoard())
            ->getQuery();

        return $query->getResult();
    }

    public function findAllWithMail()
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perId NOT IN (:perIds)')
            ->andWhere('bpv.perEmail IS NOT NULL')
            ->setParameter('perIds', self::getMemberOtTheBoard())
            ->getQuery();

        return $query->getResult();
    }

    public function getActiveUserByEmail()
    {
        $activeUsers = [];

        foreach ($this->findAll() as $bisPersonView) {
            /* @var bisPersonView $bisPersonView */
            $activeUsers[] = $bisPersonView->getEmail();
        }

        return $activeUsers;
    }

    public function getActiveUserBySfId()
    {
        $users = [];
        $repository = $this->_em->getRepository(BisPersonView::class);
        $query = $repository->createQueryBuilder('bpv')
            ->select('bpv.perId')
            ->where('bpv.perActive = :perActive')
            ->setParameter('perActive', 1)
            ->orderBy('bpv.perId', 'ASC')
            ->getQuery();

        foreach ($query->getResult() as $user) {
            $users[] = $user['perId'];
        }

        return $users;
    }

    public function getUserMobileByEmail()
    {
        $usersMobile = [];

        foreach ($this->findAll() as $bisPersonView) {
            /* @var bisPersonView $bisPersonView */
            $usersMobile[$bisPersonView->getEmail()] = ActiveDirectoryHelper::cleanUpPhoneNumber(
                $bisPersonView->getMobile()
            );
        }

        return $usersMobile;
    }

    public function getStarters(int $nbDays)
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $start = (new \DateTime())->modify('-' . $nbDays . ' days');
        $end = (new \DateTime())->modify('+' . $nbDays . ' days');

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perDateContractStart BETWEEN :start AND :end')
            ->andWhere('bpv.perDateContractStop IS NULL OR bpv.perDateContractStop > :now')
            ->andWhere("bpv.perEmail <> ''")
            ->setParameter('now', new \DateTime())
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('bpv.perDateContractStart', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    public function getFinishers(int $nbDays)
    {
        $repository = $this->_em->getRepository(BisPersonView::class);

        $start = (new \DateTime())->modify('-' . $nbDays . ' days');
        $end = (new \DateTime())->modify('+' . $nbDays . ' days');

        $query = $repository->createQueryBuilder('bpv')
            ->where('bpv.perDateContractStop BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery();

        return $query->getResult();
    }

    public function getUserChoices()
    {
        $userChoices = [];
        $repository = $this->_em->getRepository(BisPersonView::class);

        $query = $repository->createQueryBuilder('bpv')
            ->select('bpv.perEmail, bpv.perId')
            ->where('bpv.perActive = :active')
            ->setParameter('active', true)
            ->getQuery();

        $users = $query->getResult(AbstractQuery::HYDRATE_ARRAY);
        foreach ($users as $user) {
            $userChoices[$user['perEmail']] = $user['perId'];
        }

        return $userChoices;
    }
}
