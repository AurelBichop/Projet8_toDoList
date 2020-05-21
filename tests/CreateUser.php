<?php

namespace App\Tests;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

trait CreateUser
{
    /**
     * Pour la création d'un utilisateur avec le role User en Bdd
     *
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $encoder
     * @param array $overrides
     * @return User
     */
    public function userFixture(EntityManagerInterface $entityManager,UserPasswordEncoderInterface $encoder, array $overrides=[]) :User
    {
        $dataUser = array_merge([
            'username'        => 'aby',
            'email'    => 'aby@bichotte.com',
        ], $overrides);

        $user = new User();
        $user->setUsername($dataUser['username'])
             ->setEmail($dataUser['email'])
             ->setPassword($encoder->encodePassword($user,'password'));

        //enregistrement de l'utilisateur
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * Pour la création d'un utilisateur en Bdd
     *
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $encoder
     * @return User
     */
    public function adminFixture(EntityManagerInterface $entityManager,UserPasswordEncoderInterface $encoder) :User
    {
        $roleAdmin = new Role();
        $roleAdmin->setTitle("ROLE_ADMIN");
        $entityManager->persist($roleAdmin);

        $admin = new User();
        $admin->setUsername('AurelAdmin')
            ->setEmail("jsuisadmin@dansmatete.com")
            ->addRole($roleAdmin)
            ->setPassword($encoder->encodePassword($admin,'passwordAdmin'));

        //enregistrement de l'admin
        $entityManager->persist($admin);

        $entityManager->flush();

        return $admin;
    }

}