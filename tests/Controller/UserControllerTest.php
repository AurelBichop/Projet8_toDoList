<?php
namespace App\Tests\Controller;

use App\Entity\Task;
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
        //ARRANGE
        // creer plusieurs utilisateurs avec le role user et un avec le role admin
        //Création des utilisateurs
        $user = $this->userFixture($this->em, $this->encoder);
        $user2 = $this->userFixture($this->em, $this->encoder,
            [
                "username"=>"Modano",
                "email"=>"modano@mail.com"
            ]);

        //Récupération de l'admin connecté
        $admin = $this->createAdminConnected();

        $allUsers = [$user,$user2,$admin];
        //ACT
        //aller sur la route de la liste des utilisateurs
        $crawler = $this->client->request('GET', '/users');
        $responseContent = $this->client->getResponse()->getContent();

        //ASSERT
        //verifier la présence des utilisateurs créé
        foreach ($allUsers as $oneUser){
            $this->assertStringContainsString($oneUser->getUsername(), $responseContent);
            $this->assertStringContainsString($oneUser->getEmail(), $responseContent);
        }
        $this->assertCount(count($allUsers), $crawler->filter('a.btn.btn-success.btn-sm'));
    }

    // Meme test avec un access denied (user qui test)

}