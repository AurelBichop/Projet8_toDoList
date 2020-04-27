<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr-FR');

        for ($i=0; $i<10; $i++){
            $task = new Task();
            $task->setTitle($faker->text(mt_rand(5,20)))
                 ->setContent($faker->text(mt_rand(20,120)))
                 ->setCreatedAt($faker->dateTimeBetween('-10 days', 'now'));

            $manager->persist($task);
        }

        $manager->flush();
    }
}
