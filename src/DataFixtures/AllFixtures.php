<?php

namespace App\DataFixtures;

use App\Entity\Account;
use App\Entity\Application;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AllFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private $faker;

    public function load(ObjectManager $manager)
    {
        $this->faker = Factory::create();
        $this->addAdmin($manager);
        $this->addUser($manager);
        $this->addApplication($manager);
        $this->addAccount($manager);

        $manager->flush();
    }

    private function addApplication(ObjectManager $em)
    {
        for ($i = 1; $i <= 15; $i++) {
            $application = new Application();
            $application->setName($this->faker->company);
            $application->setLink($this->faker->url);
            $application->setEnable($this->faker->boolean(75));
            $em->persist($application);
        }
    }

    private function addAccount(ObjectManager $em)
    {
        for ($i = 1; $i <= 2000; $i++) {
            $account = new Account();
            $account->setEmployeeId($i);
            $account->setLastname($this->faker->lastName);
            $account->setFirstname($this->faker->firstName);
            $account->setEmail($this->faker->unique()->safeEmail);
            $account->setEmailContact($this->faker->companyEmail);
            $account->setUserPrincipalName($account->getEmail());
            $account->setAccountName($this->faker->unique()->userName);
            $account->setGeneratedPassword($this->faker->password);
            $account->setToken($account->generateToken($account->getEmail(), $account->getGeneratedPassword()));
            $account->setCreatedAt($this->faker->dateTime());
            $account->setActive($this->faker->boolean(5));
            $em->persist($account);
        }
    }

    private function addAdmin(ObjectManager $em)
    {
        $user = new User();
        $user->setEmail("damien.lagae@enabel.be");
        $user->setLastname('LAGAE');
        $user->setFirstname('Damien');
        $user->setAccountName("dlagae");
        $user->setPlainPassword('p@ssw0rd');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $em->persist($user);
    }

    private function addUser(ObjectManager $em)
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("user$i@example.org");
            $user->setAccountName("user$i");
            $user->setPlainPassword('user');
            $user->setFirstName($this->faker->firstName);
            $user->setLastName($this->faker->lastName);
            $em->persist($user);
        }
    }
}
