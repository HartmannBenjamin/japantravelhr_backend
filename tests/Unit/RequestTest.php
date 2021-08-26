<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RequestTest extends TestCase
{
    use WithFaker;

    private const ROLE_USER = 1;
    private const ROLE_HR = 2;
    private const ROLE_MANAGER = 3;

    private const STATUS_OPEN = 1;
    private const STATUS_PROCESSED = 2;
    private const STATUS_HR_REVIEWED = 3;
    private const STATUS_COMPLETE = 4;

    private $user;
    private $hr;
    private $manager;

    private $token_user;
    private $token_hr;
    private $token_manager;

    protected function setUp(): void {
        parent::setUp();

        $this->user = User::factory()->create(['role_id' => self::ROLE_USER]);
        $this->token_user = JWTAuth::fromUser($this->user);

        $this->hr = User::factory()->create(['role_id' => self::ROLE_HR]);
        $this->token_hr = JWTAuth::fromUser($this->hr);

        $this->manager = User::factory()->create(['role_id' => self::ROLE_MANAGER]);
        $this->token_manager = JWTAuth::fromUser($this->manager);

        $this->withoutExceptionHandling();
    }

    /** @test */
    public function request_all()
    {
        $this->get('api/request/all', ['authorization' => "bearer $this->token_user"])->assertStatus(200);
    }

    /** @test */
    public function create_request()
    {

        $this->post('api/request/create', [], ['authorization' => "bearer $this->token_user"])->assertStatus(404);

    }
}
