<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class UserTest
 *
 * @package Tests\Unit
 */
class UserTest extends TestCase
{
    use WithFaker;

    private const ROLE_USER = 1;
    private const ROLE_HR = 2;
    private const ROLE_MANAGER = 3;

    private $userService;

    private $user;
    private $dataTestRegister;
    private $dataTestLogin;
    private $dataTestChangePassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = new UserService();

        $this->user = User::factory()->create(['role_id' => self::ROLE_USER]);
        $password = $this->faker->password;

        $this->dataTestRegister = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'role_id' => 1,
            'password' => $password,
            'c_password' => $password,
        ];

        $this->dataTestLogin = [
            'email' => $this->faker->email(),
            'password' => $password,
        ];

        $this->dataTestChangePassword = [
            'password' => $password,
            'c_password' => $password,
        ];

        $this->withoutExceptionHandling();
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function testRoleRelation()
    {
        $user = new User();
        $user->name = 'Test';
        $user->role_id = self::ROLE_USER;

        $this->assertEquals(self::ROLE_USER, $user->role_id);
        $this->assertEquals(self::ROLE_USER, $user->role->id);

        $role = new Role(['name' => 'Test']);
        $role->save();
        $this->user->role()->associate($role);
        $this->user->save();

        $this->assertEquals($this->user->id, $role->users()->first()->id);
    }

    /**
     * @test
     */
    public function testUserResourceData()
    {
        $email = $this->faker->email();

        $user = new User();
        $user->name = 'nameTest';
        $user->email = $email;
        $user->image_name = 'testImage';
        $user->role_id = self::ROLE_USER;
        $user->password = bcrypt('testPassword');
        $user->save();

        $resource = (new \App\Http\Resources\User($user))->toArray(null);

        $this->assertEquals('nameTest', $resource['name']);
        $this->assertEquals($email, $resource['email']);
        $this->assertEquals(url('/images/testImage'), $resource['image_url']);
        $this->assertEquals(self::ROLE_USER, $resource['role']['id']);
    }

    /**
     * @test
     */
    public function testUserRoleResourceData()
    {
        $role = new Role();
        $role->name = 'testRole';

        $resource = (new \App\Http\Resources\Role($role))->toArray(null);

        $this->assertEquals('testRole', $resource['name']);
    }

    /**
     * @test
     */
    public function testValidationDataRegister()
    {
        $validator = $this->userService->validateRegisterData($this->dataTestRegister);

        $this->assertNotTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataRegisterWithWrongEmail()
    {
        $dataTest = $this->dataTestRegister;
        $badEmails = ['NotAnEmail', 'user@', '@test.com'];

        foreach ($badEmails as $badEmail) {
            $dataTest['email'] = $badEmail;
            $validator = $this->userService->validateRegisterData($dataTest);

            $this->assertTrue($validator->fails());
        }
    }

    /**
     * @test
     */
    public function testValidationDataRegisterWithWrongConfirmPassword()
    {
        $dataTest = $this->dataTestRegister;
        $dataTest['c_password'] = $this->faker->password;

        $validator = $this->userService->validateRegisterData($dataTest);

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataRegisterWithTooLongName()
    {
        $dataTest = $this->dataTestRegister;
        $dataTest['name'] = $this->faker->sentence(30);

        $validator = $this->userService->validateRegisterData($dataTest);

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataLogin()
    {
        $validator = $this->userService->validateLoginData($this->dataTestLogin);

        $this->assertNotTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataLoginWithWrongEmail()
    {
        $dataTest = $this->dataTestLogin;
        $dataTest['email'] = 'NotAnEmail';

        $validator = $this->userService->validateLoginData($dataTest);

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataLoginWithTooShortPassword()
    {
        $dataTest = $this->dataTestLogin;
        $dataTest['password'] = 's';

        $validator = $this->userService->validateLoginData($dataTest);

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataChangePassword()
    {
        $validator = $this->userService->validateChangePasswordData($this->dataTestChangePassword);

        $this->assertNotTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataChangePasswordWithWrongConfirmPassword()
    {
        $dataTest = $this->dataTestChangePassword;
        $dataTest['c_password'] = $this->faker->password;

        $validator = $this->userService->validateChangePasswordData($dataTest);

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataChangePasswordWithTooLongPassword()
    {
        $password = $this->faker->sentence(40);

        $validator = $this->userService->validateChangePasswordData(
            [
            'password' => $password,
            'c_password' => $password,
            ]
        );

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testRequestsRelation()
    {
        $this->user->requests()->create(
            [
            'subject' => 'Test Subject',
            'description' => 'Test Description',
            ]
        );

        $this->assertTrue($this->user->requests()->count() > 0);
        $this->assertEquals('Test Subject', $this->user->requests()->first()->subject);
        $this->assertEquals('Test Description', $this->user->requests()->first()->description);
    }

    /**
     * @test
     */
    public function testLogsRelation()
    {
        $this->user->logs()->create(
            [
            'request_id' => 1,
            'message' => 'Test',
            ]
        );

        $this->assertTrue($this->user->logs()->count() > 0);
        $this->assertEquals(1, $this->user->logs()->first()->request->id);
        $this->assertEquals('Test', $this->user->logs()->first()->message);
    }
}
