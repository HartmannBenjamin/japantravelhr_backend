<?php

namespace Tests\Integration;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class UserTest
 *
 * @package Tests\Integration
 */
class UserTest extends TestCase
{
    use WithFaker;

    private $user;
    private $token;
    private $password;
    private $dataUser = [
        'name' => 'name',
        'email' => 'test@mail.fr',
        'password' => 'test',
        'c_password' => 'test',
        'role_id' => UserService::ROLE_MANAGER,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $password = 'testPHPUNIT';
        $this->user = User::factory()->create(['role_id' => UserService::ROLE_USER, 'password' => bcrypt($password)]);
        $this->token = JWTAuth::fromUser($this->user);
        $this->password = $password;

        $this->withoutExceptionHandling();
    }

    /**
     * @test
     */
    public function createNewUserAndTryToLogin()
    {
        $this->get('roles')->assertStatus(200);

        $password = $this->faker->password;

        $attributes = $this->dataUser;
        $attributes['email'] = $this->faker->email;
        $attributes['password'] = $password;
        $attributes['c_password'] = $password;

        $newUser = json_decode(
            $this->post(
                'register',
                $attributes
            )->assertStatus(201)->getContent(), true
        )['data']['user'];

        $this->post('login', ['email' => $newUser['email'], 'password' => $password])
            ->assertStatus(200);
    }

    /**
     * @test
     */
    public function registerUserWithWrongPasswordData()
    {
        $attributes = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'test',
            'c_password' => 'not_same',
            'role_id' => UserService::ROLE_MANAGER,
        ];

        $this->post('register', $attributes)->assertStatus(422);
    }

    /**
     * @test
     */
    public function createNewUserWithAnExistentEmail()
    {
        $attributes = $this->dataUser;

        // email already exists in database
        $attributes['email'] = $this->user->email;

        $this->post('register', $attributes)->assertStatus(404);
    }

    /**
     * @test
     */
    public function loginAndGetUserInformation()
    {
        $this->post('login')->assertStatus(422);

        $token = json_decode(
            $this->post(
                'login',
                ['email' => $this->user->email, 'password' => $this->password]
            )->assertStatus(200)->getContent(), true
        )['token'];

        $this->get('me', ['authorization' => "bearer $token"])->assertStatus(200);
    }

    /**
     * @test
     */
    public function loginWithRandomUserCredentials()
    {
        $this->post('login', ['email' => $this->faker->email, 'password' => $this->faker->password])
            ->assertStatus(401);
    }

    /**
     * @test
     */
    public function getUserInformation()
    {
        $user = json_decode(
            $this->get(
                'me',
                ['authorization' => "bearer $this->token"]
            )->assertStatus(200)->getContent(), true
        )['data'];

        $this->assertEquals($user['name'], $this->user->name);
        $this->assertEquals($user['email'], $this->user->email);
    }

    /**
     * @test
     */
    public function tryToGetUserInformationWithInvalidToken()
    {
        $this->get(
            'me',
            ['authorization' => "bearer WrongToken"]
        )->assertStatus(401);
    }

    /**
     * @test
     */
    public function updateUserName()
    {
        $name = $this->faker->name;

        $attributes = [
            'name' => $name
        ];

        $updatedUserName = json_decode(
            $this->putJson('update', $attributes, ['authorization' => "bearer $this->token"])
                ->assertStatus(200)->getContent(), true
        )['data']['name'];

        $this->assertEquals($updatedUserName, $name);
    }

    /**
     * @test
     */
    public function updateUserWithEmptyName()
    {
        $this->putJson('update', ['name' => ''], ['authorization' => "bearer $this->token"])
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function updateUserPasswordAndTryToLogin()
    {
        $password = $this->faker->password;

        $attributes = [
            'name' => $this->user->name,
            'password' => $password,
            'c_password' => $password
        ];

        $this->putJson('update', $attributes, ['authorization' => "bearer $this->token"])
            ->assertStatus(200);

        $this->post('login', ['email' => $this->user->email, 'password' => $password])
            ->assertStatus(200);
    }

    /**
     * @test
     */
    public function updateUserWithWrongConfirmPassword()
    {
        $attributes = [
            'name' => $this->user->name,
            'password' => 'password',
            'c_password' => 'not the same'
        ];

        $this->putJson('update', $attributes, ['authorization' => "bearer $this->token"])
            ->assertStatus(422);
    }

    /**
     * @test
     */
    public function updateUserWithoutConfirmPassword()
    {
        $attributes = [
            'name' => $this->user->name,
            'password' => 'password'
        ];

        $this->putJson('update', $attributes, ['authorization' => "bearer $this->token"])
            ->assertStatus(422);
    }

    /**
     * @test
     */
    public function getUserInformationAndVerifyIt()
    {
        $jsonUser = json_decode(
            $this->get('me', ['authorization' => "bearer $this->token"])->assertStatus(200)
                ->getContent(), true
        );

        $collection = json_decode((new \App\Http\Resources\User($this->user))->toJson(), true);

        $this->assertEquals($jsonUser['data'], $collection);
    }

    /**
     * @test
     */
    public function checkIfEmailAlreadyUsedIsAvailable()
    {
        $response = json_decode(
            $this->post('emailAvailable', ['email' => $this->user->email])->assertStatus(200)
                ->getContent(), true
        );

        $this->assertFalse($response['data']);
    }

    /**
     * @test
     */
    public function checkIfRandomEmailIsAvailable()
    {
        $response = json_decode(
            $this->post('emailAvailable', ['email' => 'test@phpunit.com'])->assertStatus(200)
                ->getContent(), true
        );

        $this->assertTrue($response['data']);
    }

    /**
     * @test
     *
     * @throws Throwable
     */
    public function getRefreshTokenAndTestIt()
    {
        $this->get('me', ['authorization' => "bearer $this->token"])->assertStatus(200);

        $refreshToken = json_decode(
            $this->get('refreshToken', ['authorization' => "bearer $this->token"])->assertStatus(200)
                ->getContent(), true
        )['token'];

        $this->get('me', ['authorization' => "bearer $this->token"])->assertStatus(401);
        $this->get('me', ['authorization' => "bearer $refreshToken"])->assertStatus(200);
    }

    /**
     * @test
     *
     * @throws Throwable
     */
    public function tryToGetRefreshTokenWithoutTokenAuthorization()
    {
        $this->get('me')->assertStatus(401);
    }

    /**
     * @test
     */
    public function uploadNewUserImageAndCheckIfOldImageIsDeleted()
    {
        $image = UploadedFile::fake()->image('avatar.jpg');

        $imageUrl = json_decode(
            $this->post(
                'uploadImage',
                ['file' => $image],
                ['authorization' => "bearer $this->token"]
            )->assertStatus(200)->getContent(), true
        )['data']['image_url'];

        $this->assertStringContainsString('avatar.jpg', $imageUrl);
        $this->user->refresh();

        $imageName = $this->user->image_name;
        $this->assertEquals(url('/images/' . $imageName), $imageUrl);

        // upload new image a second time
        $this->post(
            'uploadImage',
            ['file' => $image],
            ['authorization' => "bearer $this->token"]
        )->assertStatus(200);

        // check if old image has been deleted
        $this->assertFalse(File::exists(public_path('images') . '/' . $imageName));
        $this->user->refresh();

        //delete second image
        File::delete(public_path('images') . '/' . $this->user->image_name);
    }

    /**
     * @test
     */
    public function tryToUploadNewImageWithoutAuthentication()
    {
        $image = UploadedFile::fake()->image('avatar.jpg');

        $this->post(
            'uploadImage',
            ['file' => $image],
        )->assertStatus(401);
    }

    /**
     * @test
     */
    public function tryToUploadNewImageWithoutFile()
    {
        $this->post(
            'uploadImage',
            ['file' => null],
            ['authorization' => "bearer $this->token"]
        )->assertStatus(404);
    }

    /**
     * @test
     */
    public function logoutUser()
    {
        $this->post('logout', [], ['authorization' => "bearer $this->token"])->assertStatus(200);
    }
}
