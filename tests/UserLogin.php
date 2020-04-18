<?php

namespace App\Tests;



use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait UserLogin
{
    /**
     * Creation de la session du client avec le token de connection
     * @param KernelBrowser $client
     * @param User $user
     */
    public function login(KernelBrowser $client,User $user)
    {
        $session = $client->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        $session->set('_security_main', serialize($token));
        $session->save();

        //ajout du cookie dans le client
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}