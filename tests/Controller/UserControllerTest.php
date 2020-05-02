<?php
namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\CreateUser;
use App\Tests\UserLogin;
use App\Tests\Framework\WebTestCase;

class UserControllerTest extends WebTestCase
{
    use CreateUser;
    use UserLogin;

    /**
     * Création d'un user ROLE_ADMIN et sa connection
     * @return User
     */
    private function createAdminConnected()
    {
        $admin = $this->adminFixture($this->em, $this->encoder);
        $this->login($this->client, $admin);

        return $admin;
    }

    /**
     * @test
     */
    public function index_should_list_all_users_with_admin()
    {
        // création de plusieurs utilisateurs avec le role user et un avec le role admin
        $user = $this->userFixture($this->em, $this->encoder);
        $user2 = $this->userFixture($this->em, $this->encoder,
            [
                "username"=>"Modano",
                "email"=>"modano@mail.com"
            ]);

        //Récupération de l'admin connecté
        $admin = $this->createAdminConnected();

        $allUsers = [$user,$user2,$admin];

        //aller sur la route de la liste des utilisateurs
        $crawler = $this->client->request('GET', '/users');
        $responseContent = $this->client->getResponse()->getContent();

        //verifier la présence et le nombre d'utilisateurs créé
        foreach ($allUsers as $oneUser){
            $this->assertStringContainsString($oneUser->getUsername(), $responseContent);
            $this->assertStringContainsString($oneUser->getEmail(), $responseContent);
        }
        $this->assertCount(count($allUsers), $crawler->filter('a.btn.btn-success.btn-sm'));
    }


    /**
     * @test
     */
    public function index_should_not_list_all_users_with_user_role()
    {
        $user = $this->userFixture($this->em, $this->encoder);
        $this->login($this->client, $user);

        $this->client->request('GET', '/users');

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @test
     */
    public function create_user_should_show_form()
    {
        $this->createAdminConnected();

        $this->client->request('GET', '/users/create');
        $responseContent = $this->client->getResponse()->getContent();

        $this->assertStringContainsString("name=\"user[username]\"", $responseContent);
        $this->assertStringContainsString("name=\"user[password][first]\"", $responseContent);
        $this->assertStringContainsString("name=\"user[password][second]\"", $responseContent);
        $this->assertStringContainsString("name=\"user[email]\"", $responseContent);
        $this->assertStringContainsString("name=\"user[_token]\"", $responseContent);
    }

    /**
     * @test
     */
    public function create_user_action()
    {
        //Création et connection de l'admin
        $this->createAdminConnected();

        //Act
        $crawler = $this->client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'michel',
            'user[password][first]' => 'password',
            'user[password][second]' => 'password',
            'user[email]' => 'michel@perdusonchat.com'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/users');
        $this->client->followRedirect();
        $responseContent = $this->client->getResponse()->getContent();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
        $this->assertStringContainsString('michel', $responseContent);
        $this->assertStringContainsString('michel@perdusonchat.com', $responseContent);
    }

    /**
     * @test
     */
    public function error_in_form_for_create_user_action()
    {
        $this->userFixture($this->em, $this->encoder);

        //Création et connection de l'admin
        $this->createAdminConnected();

        $crawler = $this->client->request('GET', '/users/create');

        //test d'enregistrement d'un utilisateur deja existant en Bdd
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'aby',
            'user[password][first]' => 'password',
            'user[password][second]' => 'passwordErreur',
            'user[email]' => 'aby@bichotte.com'
        ]);
        $crawler = $this->client->submit($form);
        $responseContent = $this->client->getResponse()->getContent();

        $this->assertCount(3,$crawler->filter('span.form-error-icon.badge.badge-danger.text-uppercase'));
        $this->assertStringContainsString("Cette valeur est déjà utilisée",$responseContent);
        $this->assertStringContainsString("Les deux mots de passe doivent correspondre.",$responseContent);
    }

    /**
     * @test
     */
    public function show_edit_his_user_profile()
    {
        $user = $this->userFixture($this->em, $this->encoder);

        $this->login($this->client, $user);

        $this->client->request('GET', '/compte/edit');
        $responseContent = $this->client->getResponse()->getContent();

        $this->assertStringContainsString($user->getUsername(),$responseContent);
        $this->assertStringContainsString($user->getEmail(),$responseContent);
    }





}