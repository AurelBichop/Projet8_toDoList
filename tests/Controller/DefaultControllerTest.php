<?php

use App\Entity\User;
use App\Tests\CreateUser;
use App\Tests\UserLogin;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class DefaultControllerTest extends WebTestCase
{
    use CreateUser;
    use UserLogin;


    private $client;
    private $em;
    private $encoder;

    /**
     * Declanchement avant chaque test
     * Permet de vider la base et d'initialiser les attributs
     * @throws ToolsException
     */
    protected function setUp() :void
    {
        parent::setUp();
        //Pour le boot du kernel
        $this->client = static::createClient();
        //Arrange
        //recupere l'entity manager
        $doctrine = self::$container->get('doctrine');
        $this->em = $doctrine->getManager();

        //recupere l'encoder
        $this->encoder = self::$container->get('security.user_password_encoder.generic');

        //vide la base et crée les tables
        static $metadata = null;

        if (is_null($metadata)) {
            $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        }

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();

        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
    }


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


    public function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }
}