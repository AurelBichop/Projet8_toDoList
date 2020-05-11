<?php


namespace App\Tests\Entity;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Date;

class TaskTest extends TestCase
{
    /**
     * @test
     */
    public function a_task_should_be_done(){
        //Arrange
        $task1 = new Task();

        $task2 = new Task();
        $task2->toggle(true);

        //Act
        $task1->toggle(!$task1->isDone());
        $task2->toggle(!$task2->isDone());

        //Assert
        $this->assertTrue($task1->isDone());
        $this->assertFalse($task2->isDone());
    }

    /**
     * @test
     */
    public function a_task_get_created_at(){
        $task1 = new Task();
        $dateCreated = new \DateTime();

        $task1->setCreatedAt($dateCreated);

        $this->assertSame($dateCreated, $task1->getCreatedAt());
    }
}