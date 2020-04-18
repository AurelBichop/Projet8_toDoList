<?php


namespace App\Tests\Entity;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    /**
     * @test
     */
    public function an_task_should_be_done(){
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
}