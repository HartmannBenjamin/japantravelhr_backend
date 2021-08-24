<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserApiTest extends TestCase
{
    use WithFaker;

    private const ROLE_USER = 1;
    private const ROLE_HR = 2;

    protected function setUp(): void {
        parent::setUp();

        $this->withoutExceptionHandling();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_update_user()
    {
        $user = User::factory()->create(['role_id' => self::ROLE_HR]);
        $token = JWTAuth::fromUser($user);

        $attributes = ['name' => $this->faker->name];

        $this->putJson('api/update', $attributes, ['authorization' => "bearer $token"])
            ->assertStatus(200);

        $this->assertDatabaseHas($user->getTable(), array_merge($attributes, [
            'id' => $user->id
        ]));
    }
}
