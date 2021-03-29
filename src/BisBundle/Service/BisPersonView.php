<?php

namespace BisBundle\Service;

use BisBundle\Entity\BisConjobSf;
use BisBundle\Entity\BisContractSf;
use BisBundle\Entity\BisJobSf;
use BisBundle\Entity\BisPersonSf;
use BisBundle\Repository\BisPersonViewRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class BisPersonView
 *
 * @package BisBundle\Service
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class BisPersonView
{
    /**
     * @var EntityManager
     */
    private $bis;

    /**
     * @var BisPersonViewRepository|EntityRepository
     */
    private $repository;

    public function __construct(EntityManager $bis)
    {
        $this->bis = $bis;
        $this->repository = $this->bis->getRepository(\BisBundle\Entity\BisPersonView::class);
    }

    /**
     * @param String $email
     *
     * @return \BisBundle\Entity\BisPersonView|null
     */
    public function getUser(String $email)
    {
        /** @var BisPersonViewRepository $repo*/
        $repo = $this->repository;
        return $repo->getUserByEmail($email);
    }

    /**
     * @param String $email
     *
     * @return \BisBundle\Entity\BisPersonView|null
     */
    public function getUserData(String $email)
    {
        /** @var BisPersonViewRepository $repo*/
        $repo = $this->repository;
        return $repo->getUserData($email);
    }

    /**
     * @param String $country Country code 3 letters iso
     *
     * @return \BisBundle\Entity\BisPersonView[]|null
     */
    public function getCountryUsers(String $country = null)
    {
        if (null !== $country) {
            /** @var BisPersonViewRepository $repo*/
            $repo = $this->repository;
            return $repo->getUsersByCountry($country);
        }

        /** @var BisPersonViewRepository $repo*/
        $repo = $this->repository;
        return $repo->findAllFieldUser();
    }

    public function getAllUsers()
    {
        /** @var BisPersonViewRepository $repo*/
        $repo = $this->repository;
        return $repo->findAll();
    }

    public function getActiveUserByEmail()
    {
        /** @var BisPersonViewRepository $repo*/
        $repo = $this->repository;
        return $repo->getActiveUserByEmail();
    }

    public function getActiveUserBySfId()
    {
        /** @var BisPersonViewRepository $repo*/
        $repo = $this->repository;
        return $repo->getActiveUserBySfId();
    }

    public function getUserMobileByEmail()
    {
        /** @var BisPersonViewRepository $repo*/
        $repo = $this->repository;
        return $repo->getUserMobileByEmail();
    }

    public function getStarters(int $nbDays = 15)
    {
        /** @var BisPersonViewRepository $repo*/
        $repo = $this->repository;
        return $repo->getStarters($nbDays);
    }

    public function getFinishers(int $nbDays = 15)
    {
        /** @var BisPersonViewRepository $repo*/
        $repo = $this->repository;
        return $repo->getFinishers($nbDays);
    }

    public function findById(int $id)
    {
        /** @var BisPersonViewRepository $repo*/
        $repo = $this->repository;
        return $repo->find($id);
    }

    public function cleanDataById(int $id)
    {
        // Get PersonSf
        $repoPersonSf = $this->bis->getRepository(BisPersonSf::class);
        $personSf = $repoPersonSf->find($id);

        if (null !== $personSf) {
            // Get contractSf
            $repoContractSf = $this->bis->getRepository(BisContractSf::class);
            $contractSf = $repoContractSf->findBy(['conPerId' => $personSf->getPerId()]);

            // Get conJobSf & remove BIS data for this user
            $repoConJobSf = $this->bis->getRepository(BisConjobSf::class);
            foreach ($contractSf as $contract) {
                $conJobSf = $repoConJobSf->findOneBy(['fkConId' => $contract->getConId()]);
                if (null !== $conJobSf) {
                    $this->bis->remove($conJobSf);
                }
                $this->bis->remove($contract);
            }

            // Remove PersonSf
            //            $this->em->remove($personSf);
            $this->bis->flush();
        }
    }

    /**
     * @param array $user
     *
     * @return \BisBundle\Entity\BisPersonView
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function createPerson(array $user): \BisBundle\Entity\BisPersonView
    {
        // Update or create person
        $personSf = $this->bis->find(BisPersonSf::class, $user['id']);
        if ($personSf == null) {
            $personSf = new BisPersonSf();
        }
        $personSf
            ->setPerId($user['id'])
            ->setPerEmail($user['emailEnabel'])
            ->setPerFirstname($user['firstname'])
            ->setPerLastname($user['lastname'])
            ->setPerNickname($user['nickname'])
            ->setPerActive($user['active'])
            ->setPerGender($user['gender'])
            ->setPerMotherTongue($user['motherLanguage'])
            ->setPerUsualLang($user['preferredLanguage'])
            ->setPerMobile($user['mobile'])
            ->setPerTelephone($user['phone']);

        $this->bis->persist($personSf);
        $this->bis->flush();

        // Create contract
        $perId = $this->bis->getReference(BisPersonSf::class, $personSf->getPerId());
        $contractSf = new BisContractSf();
        $contractSf
            ->setConDateStart($user['startDate'])
            ->setConDateStop($user['endDate'])
            ->setConActive($user['active'])
            ->setConPerId($perId);
        $this->bis->persist($contractSf);
        $this->bis->flush();

        // Find or create Job (position)
        if (!empty($user['position'])) {
            $position = $this->bis->find(BisJobSf::class, $user['position']);
            if (null == $position) {
                $position = new BisJobSf();
                $managerId = $this->bis->getReference(BisPersonSf::class, $user['managerId']);
                $position
                    ->setJobId($user['position'])
                    ->setJobFunction($user['jobTitle'])
                    ->setJobCountryWorkplace($user['countryWorkplace'])
                    ->setJobClass($user['jobClass'])
                    ->setJobManagerId($managerId);
                $this->bis->persist($position);
                $this->bis->flush();
            }

            // Add link contract/job
            $conJobSf = new BisConjobSf();
            $conJobSf
                ->setFkJobId($position)
                ->setFkConId($contractSf)
                ->setConjobActive($user['active'])
                ->setConjobEntryDate($user['startDate']);

            $this->bis->persist($conJobSf);
            $this->bis->flush();
        }

        // Retrieve the active record in the view bis_person
        $bisPersonView = $this->bis->find(\BisBundle\Entity\BisPersonView::class, $user['id']);

        // If the record is inactive, we make a fake record
        if (empty($bisPersonView)) {
            $bisPersonView = new \BisBundle\Entity\BisPersonView();
            $bisPersonView
                ->setId($user['id'])
                ->setEmail($personSf->getPerEmail())
                ->setFirstname($personSf->getPerFirstname())
                ->setLastname($personSf->getPerLastname())
                ->setNickname($personSf->getPerNickname())
                ->setActive($personSf->getPerActive())
                ->setTelephone($personSf->getPerTelephone())
                ->setSex($personSf->getPerGender())
                ->setLanguage($personSf->getPerUsualLang())
                ->setFunction($user['jobTitle'])
                ->setMobile($personSf->getPerMobile())
                ->setDateContractStart($user['startDate'])
                ->setDateContractStop($user['endDate'])
                ->setJobClass($user['jobClass'])
                ->setLevels([])
            ;
        }

        return $bisPersonView;
    }

    /**
     * Disable user with specific end date
     * @param int $id User id to deactivate
     * @param string $endDate The end date (format: YYYY-MM-DD)
     */
    public function disbaleUserAt(int $id, string $endDate)
    {
        $repository = $this->bis->getRepository(BisContractSf::class);
        $query = $repository
            ->createQueryBuilder('con')
            ->where('con.conPerId = :perId')
            ->andWhere('con.conActive = 1')
            ->andWhere('con.conDateStart < :today')
            ->setParameter('perId', $id)
            ->setParameter('today', date('Y-m-d'))
            ->getQuery()
        ;

        /** @var BisContractSf[] $contracts */
        $contracts = $query->getResult();

        foreach ($contracts as $contract) {
            $contract
                ->setConDateStop(new \DateTime($endDate))
                ->setConActive(0)
            ;
            $this->bis->persist($contract);
            $this->bis->flush();
        }
    }

    public function userChoices()
    {
        $choices = [
            'Select a option' => null
        ];

        /**
         * @var BisPersonViewRepository $repo
         */
        $repo = $this->repository;
        $choices+=$repo->getUserChoices();

        return $choices;
    }
}
