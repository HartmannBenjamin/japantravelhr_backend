<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use WithFaker;

    private const ROLE_USER = 1;
    private const ROLE_HR = 2;
    private const ROLE_MANAGER = 3;

    private $user;

    protected function setUp(): void {
        parent::setUp();

        $this->withoutExceptionHandling();
    }

    /** @test */
    public function checkUserRoleFunctions()
    {
        $user = new User();

        $user->role_id = self::ROLE_USER;
        $this->assertTrue($user->isUser());
        $user->role_id = self::ROLE_HR;
        $this->assertTrue($user->isHR());
        $user->role_id = self::ROLE_MANAGER;
        $this->assertTrue($user->isManager());
    }

    /** @test */
    public function testRoleRelation()
    {
        $user = new User();
        $user->role_id = self::ROLE_USER;

        $this->assertEquals(self::ROLE_USER, $user->role_id);
        $this->assertEquals(self::ROLE_USER, $user->role->id);
    }
}
