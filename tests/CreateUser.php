<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

trait CreateUser
{
    /**
     * Pour la création d'un utilisateur en Bdd
     *
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @return User
     */
    public function userFixture(EntityManagerInterface $em,UserPasswordEncoderInterface $encoder) :User
    {
        $user = new User();
        $user->setUsername('aby')
        ->setEmail('aby@bichotte.com')
        ->setPassword($encoder->encodePassword($user,'password'));

        //enregistrement de l'utilisateur
        $em->persist($user);
        $em->flush();

        return $user;
    }
}