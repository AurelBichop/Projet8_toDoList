<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends WebTestCase
{

    /**
     * @test
     *
     * Si le client n'est pas connecté
     * test de la redirection route login
     */
    public function index()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    /**
     * Methode permettant de tester avec un user connecté
     * (recuperer la session de connection)
     */


}