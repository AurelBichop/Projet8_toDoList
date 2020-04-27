<?php


namespace App\Tests\Controller;

use App\Entity\Task;
use App\Tests\CreateUser;
use App\Tests\UserLogin;
use App\Tests\Framework\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    use CreateUser;
    use UserLogin;

    private function createTask(string $titre = 'Ma premiere Tache',string $content = 'contenue de tache',$isDone = false):Task
    {
        $task = new Task();
        $task->setTitle($titre)
             ->setContent($content);

        if($isDone){
            $task->toggle(true);
        }

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }

    /**
     * @test
     */
    public function index_should_list_task_not_finish()
    {
        //Arrange
        $task1 = $this->createTask();
        $task2 = $this->createTask('Deuxieme Tache', 'Faire ma vidange');
        $task3 = $this->createTask('Troisime tache', 'réinstallation de mon serveur web');

        $listTaskNotFinish = [$task1,$task2,$task3];

        //Création de l'utilisateur
        $user = $this->userFixture($this->em, $this->encoder);
        //Creation de la session avec le token de connection
        $this->login($this->client, $user);

        //Act
        $crawler = $this->client->request('GET', '/');
        //clique sur le bouton de la liste des tâches
        $link = $crawler->selectLink('Consulter la liste des tâches à faire')->link();
        $this->client->click($link);

        $responseContent = $this->client->getResponse()->getContent();

        //Assert
        $this->assertResponseIsSuccessful();

        foreach ($listTaskNotFinish as $oneTask){
            $this->assertStringContainsString($oneTask->getTitle(), $responseContent);
            $this->assertStringContainsString($oneTask->getContent(), $responseContent);
            $this->assertFalse($oneTask->isDone());
        }
    }

    /**
     * @test
     */
    public function index_should_be_not_in_list_task_not_finish()
    {
        //Arrange
        $task1 = $this->createTask('Premiere Tache fini',' contenu d\'une tache fini',true);
        $task2 = $this->createTask('Deuxieme Tache fini', 'Faire ma vidange',true);
        $task3 = $this->createTask('Troisime tache fini', 'réinstallation de mon serveur web',true);

        $listTaskFinish = [$task1,$task2,$task3];

        //Création de l'utilisateur
        $user = $this->userFixture($this->em, $this->encoder);
        //Creation de la session avec le token de connection
        $this->login($this->client, $user);

        //Act
        $crawler = $this->client->request('GET', '/');

        //clique sur le bouton de la liste des tâches
        $link = $crawler->selectLink('Consulter la liste des tâches à faire')->link();
        $this->client->click($link);

        $responseContent = $this->client->getResponse()->getContent();

        //Assert
        $this->assertResponseIsSuccessful();

        foreach ($listTaskFinish as $oneTask){
            $this->assertStringNotContainsString($oneTask->getTitle(), $responseContent);
            $this->assertStringNotContainsString($oneTask->getContent(), $responseContent);
            $this->assertTrue($oneTask->isDone());
        }
    }

    /**
     * @test
     */
    public function list_of_task_finish(){
        //Création de l'utilisateur
        $user = $this->userFixture($this->em, $this->encoder);
        //Creation de la session avec le token de connection
        $this->login($this->client, $user);

        //3 tâches fini
        $task1 = $this->createTask('Premiere Tache fini',' contenu tache fini',true);
        $task2 = $this->createTask('Deuxieme Tache fini', 'Faire ma vidange',true);
        $task3 = $this->createTask('Troisime tache fini', 'réinstallation de mon serveur web',true);

        $listTaskFinish = [$task1,$task2,$task3];

        //Act
        $crawler = $this->client->request('GET', '/');

        //clique sur le bouton de la liste des tâches
        $link = $crawler->selectLink('Consulter la liste des tâches terminées')->link();
        $this->client->click($link);
        $responseContent = $this->client->getResponse()->getContent();

        //Assert
        $this->assertResponseIsSuccessful();

        foreach ($listTaskFinish as $oneTask){
            $this->assertStringContainsString($oneTask->getTitle(), $responseContent);
            $this->assertStringContainsString($oneTask->getContent(), $responseContent);
            $this->assertTrue($oneTask->isDone());
        }
    }


    /**
     * @test
     */
    public function toggle_task_action_should_be_done()
    {
        //Arrange
        $task1 = $this->createTask();
        //Création de l'utilisateur
        $user = $this->userFixture($this->em, $this->encoder);
        //Creation de la session avec le token de connection
        $this->login($this->client, $user);

        //Act
        $crawler = $this->client->request('GET', '/task');
        //clique sur le bouton du formulaire
        $form = $crawler->selectButton('Marquer comme faite')->form();
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $responseContent = $this->client->getResponse()->getContent();

        //Assert
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(".alert.alert-success");
        $this->assertStringContainsString($task1->getTitle(), $responseContent);
        $this->assertStringNotContainsString($task1->getContent(), $responseContent);
    }

    //A refaire en  vérifiant en bdd mofification toggle Task
    //$this->assertFalse($task1->isDone());
}