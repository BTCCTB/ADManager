<?php

namespace App\DataFixtures;

use App\Entity\Application;
use AuthBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
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
        $this->addApplication($manager);

        $manager->flush();
    }

    private function addApplication(EntityManager $em)
    {
        for ($i = 1; $i <= 15; $i++) {
            $application = new Application();
            $application->setName($this->faker->company);
            $application->setLink($this->faker->url);
            if (($i % 2) !== 0) {
                $application->setLinkFr($this->faker->url);
            }
            if (($i % 2) === 1) {
                $application->setLinkFr($this->faker->url);
                $application->setLinkNl($this->faker->url);
            }
            $application->setEnable($this->faker->boolean(75));
            $em->persist($application);
        }
    }

    private function addUser(EntityManager $em)
    {
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("damienlagae+$i@gmail.com");
            $user->setAccountName("dlagae+$i");
            $user->setPlainPassword('p@ssw0rd');
            $user->setRoles(['ROLE_ADMIN']);
            $em->persist($user);
        }

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
