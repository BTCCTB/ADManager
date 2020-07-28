<?php

namespace BisBundle\Service;

use BisBundle\Entity\BisConjobSf;
use BisBundle\Entity\BisContractSf;
use BisBundle\Entity\BisJobSf;
use BisBundle\Entity\BisPersonSf;
use BisBundle\Entity\BisPersonViewRepository;
use Doctrine\ORM\EntityManager;

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
    private $em;

    /**
     * @var BisPersonViewRepository
     */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('BisBundle:BisPersonView');
    }

    /**
     * @param String $email
     *
     * @return \BisBundle\Entity\BisPersonView|null
     */
    public function getUser(String $email)
    {
        return $this->repository->getUserByEmail($email);
    }

    /**
     * @param String $email
     *
     * @return \BisBundle\Entity\BisPersonView|null
     */
    public function getUserData(String $email)
    {
        return $this->repository->getUserData($email);
    }

    /**
     * @param String $country Country code 3 letters iso
     *
     * @return \BisBundle\Entity\BisPersonView[]|null
     */
    public function getCountryUsers(String $country = null)
    {
        if (null !== $country) {
            return $this->repository->getUsersByCountry($country);
        }

        return $this->repository->findAllFieldUser();
    }

    public function getAllUsers()
    {
        return $this->repository->findAll();
    }

    public function getActiveUserByEmail()
    {
        return $this->repository->getActiveUserByEmail();
    }

    public function getUserMobileByEmail()
    {
        return $this->repository->getUserMobileByEmail();
    }

    public function getStarters(int $nbDays = 15)
    {
        return $this->repository->getStarters($nbDays);
    }

    public function getFinishers(int $nbDays = 15)
    {
        return $this->repository->getFinishers($nbDays);
    }

    public function findById(int $id)
    {
        return $this->repository->find($id);
    }

    public function cleanDataById(int $id)
    {
        // Get PersonSf
        $repoPersonSf = $this->em->getRepository(BisPersonSf::class);
        $personSf = $repoPersonSf->find($id);

        if (null !== $personSf) {
            // Get contractSf
            $repoContractSf = $this->em->getRepository(BisContractSf::class);
            $contractSf = $repoContractSf->findBy(['conPerId' => $personSf->getPerId()]);

            // Get conJobSf & remove BIS data for this user
            $repoConJobSf = $this->em->getRepository(BisConjobSf::class);
            foreach ($contractSf as $contract) {
                $conJobSf = $repoConJobSf->findOneBy(['fkConId' => $contract->getConId()]);
                if (null !== $conJobSf) {
                    $this->em->remove($conJobSf);
                }
                $this->em->remove($contract);
            }

            // Remove PersonSf
            //            $this->em->remove($personSf);
            $this->em->flush();
        }
    }

    /**
     * @param array $user
     *
     * @return \BisBundle\Entity\BisPersonView|object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function createPerson(array $user): \BisBundle\Entity\BisPersonView
    {
        // Update or create person
        $personSf = $this->em->find(BisPersonSf::class, $user['id']);
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

        $this->em->persist($personSf);
        $this->em->flush();

        // Create contract
        $perId = $this->em->getReference(BisPersonSf::class, $personSf->getPerId());
        $contractSf = new BisContractSf();
        $contractSf
            ->setConDateStart($user['startDate'])
            ->setConDateStop($user['endDate'])
            ->setConActive($user['active'])
            ->setConPerId($perId);
        $this->em->persist($contractSf);
        $this->em->flush();

        // Find or create Job (position)
        if (!empty($user['position'])) {
            $position = $this->em->find(BisJobSf::class, $user['position']);
            if (null == $position) {
                $position = new BisJobSf();
                $managerId = $this->em->getReference(BisPersonSf::class, $user['managerId']);
                $position
                    ->setJobId($user['position'])
                    ->setJobFunction($user['jobTitle'])
                    ->setJobCountryWorkplace($user['countryWorkplace'])
                    ->setJobClass($user['jobClass'])
                    ->setJobManagerId($managerId);
                $this->em->persist($position);
                $this->em->flush();
            }

            // Add link contract/job
            $conJobSf = new BisConjobSf();
            $conJobSf
                ->setFkJobId($position)
                ->setFkConId($contractSf)
                ->setConjobActive($user['active'])
                ->setConjobEntryDate($user['startDate']);

            $this->em->persist($conJobSf);
            $this->em->flush();
        }

        // Retrieve the active record in the view bis_person
        $bisPersonView = $this->em->find(\BisBundle\Entity\BisPersonView::class, $user['id']);

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
}
