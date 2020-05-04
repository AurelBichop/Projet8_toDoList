<?php


namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Tests\CreateUser;
use App\Tests\UserLogin;
use App\Tests\Framework\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    use CreateUser;
    use UserLogin;

    /**
     * Création d'un utlisateur qui a une session de connection
     * @return User
     */
    private function userConnected(): User
    {
        //Création de l'utilisateur
        $user = $this->userFixture($this->em, $this->encoder);
        //Creation de la session avec le token de connection
        $this->login($this->client, $user);

        return $user;
    }


    /**
     * Permet de creer un Tache fixture
     * @param User $author
     * @param string $titre
     * @param string $content
     * @param bool $isDone
     * @return Task
     */
    private function createTask(User $author, string $titre = 'Ma premiere Tache',string $content = 'contenue de tache',$isDone = false):Task
    {
        $task = new Task();
        $task->setTitle($titre)
             ->setContent($content)
             ->setAuthor($author)
             ->setCreatedAt(new \DateTime());

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
        //Création et connection de l'utilisateur
        $user = $this->userConnected();

        //création des taches par l'utilisateur connecté
        $task1 = $this->createTask($user);
        $task2 = $this->createTask($user,'Deuxieme Tache', 'Faire ma vidange');
        $task3 = $this->createTask($user,'Troisime tache', 'réinstallation de mon serveur web');

        $listTaskNotFinish = [$task1,$task2,$task3];

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
            $this->assertSame($user,$oneTask->getAuthor());
        }
    }

    /**
     * @test
     */
    public function index_should_be_not_in_list_task_not_finish()
    {
        //Création et connection de l'utilisateur
        $user = $this->userConnected();

        //Arrange
        $task1 = $this->createTask($user,'Premiere Tache fini',' contenu d\'une tache fini',true);
        $task2 = $this->createTask($user,'Deuxieme Tache fini', 'Faire ma vidange',true);
        $task3 = $this->createTask($user,'Troisime tache fini', 'réinstallation de mon serveur web',true);

        $listTaskFinish = [$task1,$task2,$task3];

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

        //Création et connection de l'utilisateur
        $user = $this->userConnected();

        //tâches fini
        $task1 = $this->createTask($user,'Premiere Tache fini',' contenu tache fini',true);
        $task2 = $this->createTask($user,'Deuxieme Tache fini', 'Faire ma vidange',true);
        $task3 = $this->createTask($user,'Troisime tache fini', 'réinstallation de mon serveur web',true);

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
            $this->assertSame($user,$oneTask->getAuthor());
        }
    }


    /**
     * @test
     */
    public function toggle_task_action_should_be_done()
    {
        //Création et connection de l'utilisateur
        $user = $this->userConnected();
        //Arrange
        $task1 = $this->createTask($user);

        //Act
        $crawler = $this->client->request('GET', '/task');
        //clique sur le bouton du formulaire
        $form = $crawler->selectButton('Marquer comme faite')->form();
        $this->client->submit($form);
        $this->client->followRedirect();

        $responseContent = $this->client->getResponse()->getContent();

        //Assert
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(".alert.alert-success");
        $this->assertStringContainsString($task1->getTitle(), $responseContent);
        $this->assertStringNotContainsString($task1->getContent(), $responseContent);
    }

    /**
     * @test
     */
    public function create_task_action()
    {
        //Création et connection de l'utilisateur
        $this->userConnected();

        //Act
        $crawler = $this->client->request('GET', '/tasks/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'Tache test',
            'task[content]' => 'je suis une tache generé par un test fonctionnel'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/task');
        $this->client->followRedirect();
        $responseContent = $this->client->getResponse()->getContent();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
        $this->assertStringContainsString('Tache test', $responseContent);
        $this->assertStringContainsString('je suis une tache generé par un test fonctionnel', $responseContent);
    }

    /**
     * @test
     */
    public function edit_task_action()
    {
        //Création et connection de l'utilisateur
        $user = $this->userConnected();
        //Création d'une tache à éditer
        $task1 = $this->createTask($user);

        //Act
        $crawler = $this->client->request('GET', '/tasks/'.$task1->getId().'/edit');
        $responseContent = $this->client->getResponse()->getContent();

        //check le contenu du formulaire d'edition
        $this->assertStringContainsString($task1->getTitle(), $responseContent);
        $this->assertStringContainsString($task1->getContent(), $responseContent);

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'Tache modifié',
            'task[content]' => 'je suis une tache Mofifié par un test'
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();
        $responseContent = $this->client->getResponse()->getContent();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
        $this->assertStringContainsString('Tache modifié', $responseContent);
        $this->assertStringContainsString('je suis une tache Mofifié par un test', $responseContent);
    }

    /**
     * @test
     */
    public function delete_task_action(){
        //Création et connection de l'utilisateur
        $user = $this->userConnected();
        //Création d'une tache à éditer
        $task1 = $this->createTask($user);

        //Act
        $crawler = $this->client->request('GET', '/task');
        //clique sur le bouton du formulaire
        $form = $crawler->selectButton('Supprimer')->form();
        $this->client->submit($form);
        $this->client->followRedirect();

        $responseContent = $this->client->getResponse()->getContent();

        //Assert
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(".alert.alert-success");
        $this->assertStringNotContainsString($task1->getTitle(), $responseContent);
        $this->assertStringNotContainsString($task1->getContent(), $responseContent);
    }

    /**
     * @test
     */
    public function task_anonymous_for_admin(){
        //faire une query avec em pour creer une tache sans auteur
        $sql = "INSERT INTO task (created_at, title, content) VALUES (NOW(), 'tache ano', 'a anonymous task')";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute([]);

        //recuperer l'admin et voir si la tache est dans sa liste
        $admin = $this->adminFixture($this->em, $this->encoder);
        $this->login($this->client, $admin);

        $this->client->request('GET', '/task');
        $responseContent = $this->client->getResponse()->getContent();

        $this->assertStringContainsString('tache ano', $responseContent);
        $this->assertStringContainsString('a anonymous task', $responseContent);
    }
}