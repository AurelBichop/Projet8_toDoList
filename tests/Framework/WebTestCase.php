<?php

namespace App\Tests\Framework;

use App\Tests\Framework\Traits\RefreshDataBase;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    use RefreshDataBase;

    protected $client;
    protected $em;
    protected $encoder;


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
        $this->refreshDataBase();
    }

    //close l'entity manager (evite les fuites de mémoires)
    public function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }
}