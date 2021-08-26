<?php

namespace Tests\Unit;

use App\Models\Request;
use App\Models\User;
use App\Services\RequestService;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RequestTest extends TestCase
{
    use WithFaker;

    private const ROLE_USER = 1;
    private const ROLE_HR = 2;
    private const ROLE_MANAGER = 3;

    private const STATUS_OPEN = RequestService::STATUS_OPEN;
    private const STATUS_PROCESSED = RequestService::STATUS_PROCESSED;
    private const STATUS_HR_REVIEWED = RequestService::STATUS_HR_REVIEWED;
    private const STATUS_COMPLETE = RequestService::STATUS_COMPLETE;

    private $token_user;
    private $token_hr;
    private $token_manager;
    private $otherUserToken;
    private $request;
    private $user;

    protected function setUp(): void {
        parent::setUp();

        $this->user = User::factory()->create(['role_id' => self::ROLE_USER]);
        $this->token_user = JWTAuth::fromUser($this->user);

        $hr = User::factory()->create(['role_id' => self::ROLE_HR]);
        $this->token_hr = JWTAuth::fromUser($hr);

        $manager = User::factory()->create(['role_id' => self::ROLE_MANAGER]);
        $this->token_manager = JWTAuth::fromUser($manager);

        $randomOtherUser = User::factory()->create(['role_id' => self::ROLE_USER]);
        $this->otherUserToken = JWTAuth::fromUser($randomOtherUser);

        $this->request = Request::factory()->create(['status_id' => self::STATUS_OPEN]);

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
        $this->post('api/request/create', [], ['authorization' => "bearer $this->token_user"])
            ->assertStatus(404);

        $attributes = [
            'subject' => 'This is a test',
            'description' => 'Here\'s the description'
        ];

        $request = json_decode($this->post(
            'api/request/create',
            $attributes,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(201)->getContent(), true)['data'];


        $this->assertEquals(
            [$attributes['subject'], $attributes['description']],
            [$request['subject'], $request['description']]
        );

        $this->assertEquals(self::STATUS_OPEN, $request['status']['id']);
    }

    /** @test */
    public function request_show()
    {
        $attributes = [
            'subject' => 'This is a test',
            'description' => 'Here\'s the description'
        ];

        $requestId = json_decode($this->post(
            'api/request/create',
            $attributes,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(201)->getContent(), true)['data']['id'];

        $request = json_decode($this->get(
            'api/request/get/' . $requestId,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(200)->getContent(), true)['data'];

        # as a hr staff
        $this->get('api/request/get/' . $requestId, ['authorization' => "bearer $this->token_hr"])
            ->assertStatus(200);


        $this->get('api/request/get/' . $requestId)->assertStatus(401);

        $this->get('api/request/get/' . $requestId, ['authorization' => "bearer $this->otherUserToken"])
            ->assertStatus(403);

        $this->assertEquals(
            [$attributes['subject'], $attributes['description']],
            [$request['subject'], $request['description']]
        );
    }

    /** @test */
    public function update_request()
    {
        $attributes = [
            'subject' => 'This is a test',
            'description' => 'Here\'s the description'
        ];

        $requestId = json_decode($this->post(
            'api/request/create',
            $attributes,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(201)->getContent(), true)['data']['id'];

        $attributesUpdated = [
            'subject' => 'This is a test updated',
            'description' => 'Here\'s the description updated'
        ];

        $request = json_decode($this->put(
            'api/request/edit/' . $requestId,
            $attributesUpdated,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(200)->getContent(), true)['data'];

        $this->assertNotEquals(
            [$attributes['subject'], $attributes['description']],
            [$request['subject'], $request['description']]
        );

        $this->assertEquals(
            [$attributesUpdated['subject'], $attributesUpdated['description']],
            [$request['subject'], $request['description']]
        );

        $this->put(
            'api/request/edit/' . $requestId,
            $attributesUpdated,
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(403);

        $this->put(
            'api/request/edit/' . $requestId,
            $attributesUpdated,
            ['authorization' => "bearer $this->otherUserToken"]
        )->assertStatus(403);
    }

    /** @test */
    public function change_request_status_hr()
    {
        $this->request->status_id = self::STATUS_OPEN;
        $this->request->save();

        $requestStatusId = json_decode($this->put(
            'api/request/changeStatus/' . $this->request->id,
            ['status_id' => self::STATUS_PROCESSED],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(200)->getContent(), true)['data']['status']['id'];

        $this->assertEquals(self::STATUS_PROCESSED, $requestStatusId);

        $this->put(
            'api/request/changeStatus/' . $this->request->id,
            ['status_id' => 6],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(404);

        $this->put(
            'api/request/changeStatus/' . $this->request->id,
            ['status_id' => self::STATUS_PROCESSED],
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /** @test */
    public function complete_request_manager()
    {
        $this->request->status_id = self::STATUS_HR_REVIEWED;
        $this->request->save();

        $requestStatusId = json_decode($this->put(
            'api/request/complete/' . $this->request->id,
            [],
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(200)->getContent(), true)['data']['status']['id'];

        $this->assertEquals(self::STATUS_COMPLETE, $requestStatusId);

        $this->request->status_id = self::STATUS_HR_REVIEWED;
        $this->request->save();

        $this->put(
            'api/request/complete/' . $this->request->id,
            [],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(403);
    }

    /** @test */
    public function request_status_open()
    {
        $request = Request::factory()->create(['user_id' => $this->user->id]);

        $this->put(
            'api/request/edit/' . $request->id,
            ['subject' => 'test subject', 'description' => 'This is a test description'],
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(200);

        $this->put(
            'api/request/changeStatus/' . $request->id,
            ['status_id' => self::STATUS_PROCESSED],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(200);

        $this->put(
            'api/request/complete/' . $request->id,
            [],
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(403);
    }

    /** @test */
    public function request_status_processed()
    {
        $request = Request::factory()->create(['user_id' => $this->user->id, 'status_id' => self::STATUS_PROCESSED]);

        $this->put(
            'api/request/edit/' . $request->id,
            ['subject' => 'test subject', 'description' => 'This is a test description'],
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);

        $this->put(
            'api/request/changeStatus/' . $request->id,
            ['status_id' => self::STATUS_PROCESSED],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(200);

        $this->put(
            'api/request/complete/' . $request->id,
            [],
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(403);
    }

    /** @test */
    public function request_status_hr_reviewed()
    {
        $request = Request::factory()->create(['user_id' => $this->user->id, 'status_id' => self::STATUS_HR_REVIEWED]);

        $this->put(
            'api/request/edit/' . $request->id,
            ['subject' => 'test subject', 'description' => 'This is a test description'],
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);

        $this->put(
            'api/request/changeStatus/' . $request->id,
            ['status_id' => self::STATUS_HR_REVIEWED],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(200);

        $this->put(
            'api/request/complete/' . $request->id,
            [],
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(200);
    }


    /** @test */
    public function request_status_hr_complete()
    {
        $request = Request::factory()->create(['user_id' => $this->user->id, 'status_id' => self::STATUS_COMPLETE]);

        $this->put(
            'api/request/edit/' . $request->id,
            ['subject' => 'test subject', 'description' => 'This is a test description'],
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);

        $this->put(
            'api/request/changeStatus/' . $request->id,
            ['status_id' => self::STATUS_HR_REVIEWED],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(403);

        $this->put(
            'api/request/complete/' . $request->id,
            [],
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(403);
    }


    /** @test */
    public function request_status()
    {
        $this->get('api/request/status', ['authorization' => "bearer $this->token_user"])->assertStatus(200);
    }

    /** @test */
    public function request_pdf()
    {
        $this->get('api/request/pdf', ['authorization' => "bearer $this->token_user"])->assertStatus(403);
        $this->get('api/request/pdf', ['authorization' => "bearer $this->token_hr"])->assertStatus(200);
        $this->get('api/request/pdf', ['authorization' => "bearer $this->token_manager"])->assertStatus(200);
    }
}
