<?php

use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class DefaultControllerTest extends WebTestCase
{

    /**
     * @test
     *
     * Si le client n'est pas connecté
     * test de la redirection route login
     */
    public function index_redirect_login()
    {
        //Client non connecté
        $client = static::createClient();

        //Act
        $client->request('GET', '/');
        $client->followRedirect();

        $responseContent = $client->getResponse()->getContent();

        //Assert
        $this->assertResponseIsSuccessful();
        $this->assertRegExp(sprintf('/%s$/', 'login'), $client->getRequest()->getUri());
        $this->assertStringContainsString('Nom d\'utilisateur :', $responseContent);
        $this->assertStringContainsString('Mot de passe :', $responseContent);
    }

    /**
     * @test
     * Methode permettant de tester avec un user connecté
     * (recupere la session de connection)
     *
     * A REFACTORISER
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function index_with_connection()
    {
        //Pour le boot du kernel
        $client = static::createClient();

        //Arrange
        //recupere l'entity manager
        $doctrine = self::$container->get('doctrine');
        $em = $doctrine->getManager();

        //recupere l'encoder
        $encoder = self::$container->get('security.user_password_encoder.generic');

        //vide la base et crée la table
        static $metadata = null;

        if (is_null($metadata)) {
            $metadata = $em->getMetadataFactory()->getAllMetadata();
        }

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();

        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }

        ////:::::::::::::::::::::::::

        //Création de l'utilisateur
        $user = new User();
        $user->setUsername('aby')
             ->setEmail('aby@bichotte.com')
             ->setPassword($encoder->encodePassword($user,'password'));

        //enregistrement de l'utilisateur
        $em->persist($user);
        $em->flush();
        ////

        //Creation de la session avec le token de connection
        $session = $client->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());


        $session->set('_security_main', serialize($token));
        $session->save();


        //ajout du cookie dans le client
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);


        //Act
        $client->request('GET', '/');

        //récupere le contenu de la page
        $responseContent = $client->getResponse()->getContent();

        //Assert
        $this->assertResponseIsSuccessful();
        $this->assertRegExp(sprintf('/%s$/', ''), $client->getRequest()->getUri());
        $this->assertStringContainsString('Bienvenue sur Todo List', $responseContent);
        $this->assertStringContainsString('Se déconnecter', $responseContent);
    }

}