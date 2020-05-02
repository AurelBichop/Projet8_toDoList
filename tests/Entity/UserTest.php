<?php


namespace App\Tests\Entity;

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

        $roles = $user->getRoles();

        $checkRoleUser = array_search("ROLE_USER",$roles);
        $checkRoleAdmin = array_search("ROLE_ADMIN",$roles);

        $this->assertIsNotBool($checkRoleUser);
        $this->assertFalse($checkRoleAdmin);
    }
}