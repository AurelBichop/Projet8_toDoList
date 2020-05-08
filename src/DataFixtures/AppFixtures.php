<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\Task;
use App\Entity\User;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr-FR');

        //creation du role Admin
        $roleAdmin = new Role();
        $roleAdmin->setTitle("ROLE_ADMIN");
        $manager->persist($roleAdmin);

        //création des utilisateurs
        $user = new User();
        $user->setUsername("UserName")
             ->setEmail("usermail@exemple.com")
             ->setPassword($this->encoder->encodePassword($user,'password'));
        $manager->persist($user);

        $admin = new User();
        $admin->setUsername('AurelAdmin')
            ->setEmail("jsuisadmin@dansmatete.com")
            ->addRole($roleAdmin)
            ->setPassword($this->encoder->encodePassword($admin,'passwordAdmin'));
        $manager->persist($admin);

        $tabUsers = [$user,$admin];

        //création des taches liée aux utilisateurs + anonyme
        for ($i=0; $i<10; $i++){
            $task = new Task();
            $task->setTitle($faker->text(mt_rand(5,20)))
                 ->setContent($faker->text(mt_rand(20,120)))
                 ->setAuthor($tabUsers[mt_rand(0,1)])
                 ->setCreatedAt($faker->dateTimeBetween('-10 days', 'now'));

            $manager->persist($task);
        }

        //Création des taches anonymes
        for ($i=0; $i<5; $i++){
            $taskAnonyme = new Task();
            $taskAnonyme->setTitle($faker->text(mt_rand(5,20)))
                ->setContent($faker->text(mt_rand(20,120)))
                ->setCreatedAt($faker->dateTimeBetween('-10 days', 'now'));

            $manager->persist($taskAnonyme);
        }

        $manager->flush();
    }
}
