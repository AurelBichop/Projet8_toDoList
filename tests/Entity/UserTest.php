<?php


namespace App\Tests\Entity;

use App\Entity\Role;
use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @test
     */
    public function a_user_got_just_user_role_by_default()
    {
        $user = new User();

        $rolesUser = $user->getRoles();

        $checkRoleUser = array_search("ROLE_USER",$rolesUser);
        $checkRoleAdmin = array_search("ROLE_ADMIN",$rolesUser);

        $this->assertIsNotBool($checkRoleUser);
        $this->assertFalse($checkRoleAdmin);
    }

    /**
     * @test
     */
    public function a_user_with_admin_role()
    {
        $user = new User();
        $user->setUsername("UserTest");

        $roleAdmin = new Role();
        $roleAdmin->setTitle("ROLE_ADMIN");

        $user->addRole($roleAdmin);
        $rolesUser = $user->getRoles();


        $this->assertCount(2,$rolesUser);
        $this->assertSame("ROLE_USER",$user->getRoles()[1]);
        $this->assertSame("ROLE_ADMIN",$user->getRoles()[0]);
        $this->assertSame("UserTest", $roleAdmin->getUsers()[0]->getUsername());
    }

    /**
     * @test
     */
    public function remove_admin_role()
    {
        $user = new User();

        $roleAdmin = new Role();
        $roleAdmin->setTitle("ROLE_ADMIN");

        $user->addRole($roleAdmin);
        $user->removeRole($roleAdmin);

        $rolesUser = $user->getRoles();
        $checkRoleAdmin = array_search("ROLE_ADMIN",$rolesUser);

        $this->assertCount(1,$rolesUser);
        $this->assertFalse($checkRoleAdmin);
    }

    /**
     * @test
     */
    public function add_a_task()
    {
        $user = new User();

        $task = new Task();
        $task->setTitle("Tache de test")
             ->setContent("contenu de la tache");

        $task2 = new Task();
        $task2->setTitle("Tache de test2")
            ->setContent("contenu de la tache2");

        $user->addTask($task)
             ->addTask($task2);

        $this->assertSame($task->getTitle(),$user->getTasks()[0]->getTitle());
        $this->assertSame($task->getContent(),$user->getTasks()[0]->getContent());
        $this->assertSame($task2->getTitle(),$user->getTasks()[1]->getTitle());
        $this->assertSame($task2->getContent(),$user->getTasks()[1]->getContent());
    }

    /**
     * @test
     */
    public function remove_a_task()
    {
        $user = new User();

        $task = new Task();
        $task->setTitle("Tache de test")
            ->setContent("contenu de la tache");

        $user->addTask($task);

        $taskUser = $user->getTasks();
        $this->assertCount(1,$taskUser);

        $user->removeTask($task);
        $this->assertCount(0,$taskUser);
    }
}