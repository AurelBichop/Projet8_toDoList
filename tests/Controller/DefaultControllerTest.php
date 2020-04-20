<?php

namespace App\Tests\Controller;

use App\Tests\CreateUser;
use App\Tests\UserLogin;
use App\Tests\Framework\WebTestCase;


class DefaultControllerTest extends WebTestCase
{
    use CreateUser;
    use UserLogin;

    /**
     * @test
     *
     * Si le client n'est pas connecté
     * test de la redirection route login
     */
    public function index_redirect_login()
    {
        //Act
        $this->client->request('GET', '/');
        $this->client->followRedirect();

        $responseContent = $this->client->getResponse()->getContent();

        //Assert
        $this->assertResponseIsSuccessful();
        $this->assertRegExp(sprintf('/%s$/', 'login'), $this->client->getRequest()->getUri());
        $this->assertSelectorNotExists('.alert.alert-danger');
        $this->assertStringContainsString('Nom d\'utilisateur :', $responseContent);
        $this->assertStringContainsString('Mot de passe :', $responseContent);
    }


    /**
     * @test
     *
     * Methode permettant de tester avec un user connecté
     * (recupere la session de connection)
     */
    public function index_with_user_connected()
    {
        //Création de l'utilisateur
        $user = $this->userFixture($this->em, $this->encoder);
        //Creation de la session avec le token de connection
        $this->login($this->client, $user);
        //Act
        $this->client->request('GET', '/');
        //récupere le contenu de la page
        $responseContent = $this->client->getResponse()->getContent();

        //Assert
        $this->assertResponseIsSuccessful();
        //vérification que nous sommes pas redirigé
        $this->assertRegExp(sprintf('/%s$/', ''), $this->client->getRequest()->getUri());
        //vérification du contenu de la page
        $this->assertStringContainsString('Bienvenue sur Todo List', $responseContent);
        $this->assertStringContainsString('Se déconnecter', $responseContent);
    }

    /**
     * @test
     */
    public function login_with_bad_credential()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
                '_username' => 'avrel',
                '_password' => '00000'
            ]
        );
        $this->client->submit($form);
        $this->client->followRedirect();

        //récupere le contenu de la page
        $responseContent = $this->client->getResponse()->getContent();

        //affirme l'url
        $this->assertRegExp(sprintf('/%s$/', 'login'), $this->client->getRequest()->getUri());

        //check le contenu de la page avec le message d'alert
        $this->assertSelectorExists('.alert.alert-danger');
        $this->assertStringContainsString('Nom d\'utilisateur :', $responseContent);
        $this->assertStringContainsString('Mot de passe :', $responseContent);
    }

    /**
     * @test
     */
    public function login_with_good_credential()
    {
        //Création de l'utilisateur
        $user = $this->userFixture($this->em, $this->encoder);

        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
                '_username' => $user->getUsername(),
                '_password' => 'password'
            ]
        );
        $this->client->submit($form);
        $this->client->followRedirect();

        //affirme l'url
        $this->assertRegExp(sprintf('/%s$/', ''), $this->client->getRequest()->getUri());
        //check le contenu de la page
        $this->assertSelectorExists('.btn.btn-info');
        $this->assertSelectorTextContains('h1','Bienvenue sur Todo List');
    }
}