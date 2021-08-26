<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTest extends TestCase
{
    use WithFaker;

    private const ROLE_USER = 1;
    private const ROLE_HR = 2;
    private const ROLE_MANAGER = 3;

    private $user;
    private $token;
    private $password;

    protected function setUp(): void {
        parent::setUp();

        $password = 'testPHPUNIT';
        $this->user = User::factory()->create(['role_id' => self::ROLE_USER, 'password' => bcrypt($password)]);
        $this->token = JWTAuth::fromUser($this->user);
        $this->password = $password;

        $this->withoutExceptionHandling();
    }

    /** @test */
    public function user_register()
    {
        $password = $this->faker->password;

        $attributes = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $password,
            'c_password' => 'test',
            'role_id' => self::ROLE_MANAGER,
        ];

        $this->post('api/register', $attributes)->assertStatus(422);

        $attributes['c_password'] = $password;
        $newUser =json_decode($this->post(
            'api/register',
            $attributes
        )->assertStatus(201)->getContent(), true)['data']['user'];

        $attributes['email'] = $this->user->email;
        $this->post('api/register', $attributes)->assertStatus(404);

        $this->post('api/login', ['email' => $newUser['email'], 'password' => $password])
            ->assertStatus(200);
    }

    /**
     * @test
     *
     * @throws Throwable
     */
    public function user_login()
    {
        $this->post('api/login')->assertStatus(422);

        $this->post('api/login', ['email' => $this->faker->email, 'password' => $this->faker->password])
            ->assertStatus(401);

        $token = json_decode($this->post(
            'api/login',
            ['email' => $this->user->email, 'password' => $this->password]
        )->assertStatus(200)->decodeResponseJson()->json, true)['token'];

        $this->get('api/me', ['authorization' => "bearer $this->token"])->assertStatus(200);
        $this->get('api/me', ['authorization' => "bearer $token"])->assertStatus(200);
    }

    /** @test */
    public function user_role()
    {
        $user = new User();

        $user->role_id = self::ROLE_USER;
        $this->assertTrue($user->isUser());
        $user->role_id = self::ROLE_HR;
        $this->assertTrue($user->isHR());
        $user->role_id = self::ROLE_MANAGER;
        $this->assertTrue($user->isManager());
    }

    /**
     * @test
     *
     * @throws Throwable
     */
    public function update_user()
    {
        $jsonUser = json_decode($this->get('api/me', ['authorization' => "bearer $this->token"])->assertStatus(200)
            ->decodeResponseJson()->json, true);

        $attributes = ['name' => $this->faker->name];

        $jsonUserUpdated = json_decode($this->putJson('api/update', $attributes, ['authorization' => "bearer $this->token"])
            ->assertStatus(200)->decodeResponseJson()->json, true);

        $this->assertDatabaseHas($this->user->getTable(), array_merge($attributes, [
            'id' => $this->user->id
        ]));

        $this->assertNotEquals($jsonUser['data']['name'], $jsonUserUpdated['data']['name']);

        unset($jsonUser['data']['name']);
        unset($jsonUserUpdated['data']['name']);

        $this->assertEquals($jsonUser['data'], $jsonUserUpdated['data']);
    }

    /**
     * @test
     *
     * @throws Throwable
     */
    public function update_user_password()
    {
        $this->putJson('api/update', ['name' => ''], ['authorization' => "bearer $this->token"])
            ->assertStatus(404);

        $password = $this->faker->password;
        $attributes = ['name' => $this->faker->name, 'password' => $password];

        $this->putJson('api/update', $attributes, ['authorization' => "bearer $this->token"])
            ->assertStatus(422);

        $attributes['c_password'] = 'test';

        $this->putJson('api/update', $attributes, ['authorization' => "bearer $this->token"])
            ->assertStatus(422);

        $attributes['c_password'] =  $password;

        $this->putJson('api/update', $attributes, ['authorization' => "bearer $this->token"])
            ->assertStatus(200);
    }

    /** @test
     *
     * @throws Throwable
     */
    public function get_user_information()
    {
        $jsonUser = json_decode($this->get('api/me', ['authorization' => "bearer $this->token"])->assertStatus(200)
            ->decodeResponseJson()->json, true);

        $collection = json_decode((new \App\Http\Resources\User($this->user))->toJson(), true);

        $this->assertEquals($jsonUser['data'], $collection);
    }

    /** @test */
    public function email_available()
    {
        $response = json_decode($this->post('api/emailAvailable', ['email' => 'test@phpunit.com'])->assertStatus(200)
            ->getContent(), true);

        $this->assertTrue($response['data']);

        $response = json_decode($this->post('api/emailAvailable', ['email' => $this->user->email])->assertStatus(200)
            ->getContent(), true);

        $this->assertFalse($response['data']);
    }

    /**
     * @test
     *
     * @throws Throwable
     */
    public function refresh_token()
    {
        $this->get('api/me', ['authorization' => "bearer $this->token"])->assertStatus(200);

        $response = json_decode($this->get('api/refreshToken', ['authorization' => "bearer $this->token"])->assertStatus(200)
            ->getContent(), true);

        $refreshToken = $response['token'];

        $this->get('api/refreshToken', ['authorization' => "bearer $this->token"])->assertStatus(401);

        $this->get('api/refreshToken', ['authorization' => "bearer $refreshToken"])->assertStatus(200);
    }

    /** @test */
    public function user_logout()
    {
        $this->post('api/logout', [], ['authorization' => "bearer $this->token"])->assertStatus(200);
    }
}
