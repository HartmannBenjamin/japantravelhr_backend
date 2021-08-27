<?php

namespace Tests\Integration;

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

    private $token_user;
    private $token_hr;
    private $token_manager;
    private $request;
    private $user;
    private $dataRequest = [
        'subject' => 'This is a test',
        'description' => 'Here\'s the description'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role_id' => self::ROLE_USER]);
        $this->token_user = JWTAuth::fromUser($this->user);

        $hr = User::factory()->create(['role_id' => self::ROLE_HR]);
        $this->token_hr = JWTAuth::fromUser($hr);

        $manager = User::factory()->create(['role_id' => self::ROLE_MANAGER]);
        $this->token_manager = JWTAuth::fromUser($manager);

        $this->request = Request::factory()->create(['status_id' => self::STATUS_OPEN]);

        $this->withoutExceptionHandling();
    }

    /** @test */
    public function request_all()
    {
        $this->get('api/request/all', ['authorization' => "bearer $this->token_user"])->assertStatus(200);
    }

    /** @test */
    public function createRequestWithoutData()
    {
        $this->post('api/request/create', [], ['authorization' => "bearer $this->token_user"])
            ->assertStatus(404);
    }

    /** @test */
    public function createRequestWithCommonData()
    {
        $request = json_decode($this->post(
            'api/request/create',
            $this->dataRequest,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(201)->getContent(), true)['data'];


        $this->assertEquals(
            [$this->dataRequest['subject'], $this->dataRequest['description']],
            [$request['subject'], $request['description']]
        );

        $this->assertEquals(self::STATUS_OPEN, $request['status']['id']);
    }

    /** @test */
    public function getRequestByIdAsUserWhoDontCreateIt() {
        $this->get('api/request/get/' . $this->request->id, ['authorization' => "bearer $this->token_user"])
            ->assertStatus(403);
    }

    /** @test */
    public function getRequestByIdAsHrStaff() {
        $this->get('api/request/get/' . $this->request->id, ['authorization' => "bearer $this->token_hr"])
            ->assertStatus(200);
    }

    /** @test */
    public function getRequestByIdAsManagerStaff() {
        $this->get('api/request/get/' . $this->request->id, ['authorization' => "bearer $this->token_manager"])
            ->assertStatus(200);
    }

    /** @test */
    public function createRequestAndVerifyInformation()
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

        $this->assertEquals(
            [$attributes['subject'], $attributes['description']],
            [$request['subject'], $request['description']]
        );
    }

    /** @test */
    public function createRequestAndUpdateIt()
    {
        # creation of request
        $requestId = json_decode($this->post(
            'api/request/create',
            $this->dataRequest,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(201)->getContent(), true)['data']['id'];

        $attributesUpdated = [
            'subject' => 'This is a test updated',
            'description' => 'Here\'s the description updated'
        ];

        # update data of request
        $request = json_decode($this->put(
            'api/request/edit/' . $requestId,
            $attributesUpdated,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(200)->getContent(), true)['data'];

        # verify data is updated
        $this->assertNotEquals(
            [$this->dataRequest['subject'], $this->dataRequest['description']],
            [$request['subject'], $request['description']]
        );

        # verify data is correct
        $this->assertEquals(
            [$attributesUpdated['subject'], $attributesUpdated['description']],
            [$request['subject'], $request['description']]
        );
    }

    /** @test */
    public function tryToUpdateARequestAsOtherUser() {
        $this->put(
            'api/request/edit/' . $this->request->id,
            $this->dataRequest,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /** @test */
    public function tryToUpdateARequestAsHrStaff() {
        $this->put(
            'api/request/edit/' . $this->request->id,
            $this->dataRequest,
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(403);
    }

    /** @test */
    public function tryToUpdateARequestAsManager() {
        $this->put(
            'api/request/edit/' . $this->request->id,
            $this->dataRequest,
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(403);
    }

    /** @test */
    public function tryToUpdateAProcessedRequest() {
        $request = Request::factory()->create(['user_id' => $this->user->id, 'status_id' => self::STATUS_PROCESSED]);

        $this->put(
            'api/request/edit/' . $request->id,
            $this->dataRequest,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /** @test */
    public function tryToUpdateAHrReviewedRequest() {
        $request = Request::factory()->create(['user_id' => $this->user->id, 'status_id' => self::STATUS_HR_REVIEWED]);

        $this->put(
            'api/request/edit/' . $request->id,
            $this->dataRequest,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /** @test */
    public function changeRequestStatusAsHrStaff()
    {
        $requestStatusId = json_decode($this->put(
            'api/request/changeStatus/' . $this->request->id,
            ['status_id' => self::STATUS_HR_REVIEWED],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(200)->getContent(), true)['data']['status']['id'];

        $this->assertEquals(self::STATUS_HR_REVIEWED, $requestStatusId);
    }

    /** @test */
    public function changeRequestStatusPassingNonexistentStatusId()
    {
        $this->put(
            'api/request/changeStatus/' . $this->request->id,
            ['status_id' => 6],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(404);
    }

    /** @test */
    public function changeRequestStatusAsUser()
    {
        $this->put(
            'api/request/changeStatus/' . $this->request->id,
            ['status_id' => self::STATUS_PROCESSED],
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /** @test */
    public function changeRequestStatusAsManager()
    {
        $this->put(
            'api/request/changeStatus/' . $this->request->id,
            ['status_id' => self::STATUS_PROCESSED],
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(403);
    }

    /** @test */
    public function completeRequestAsManager()
    {
        # update request status
        $this->request->status_id = self::STATUS_HR_REVIEWED;
        $this->request->save();

        $requestStatusId = json_decode($this->put(
            'api/request/complete/' . $this->request->id,
            [],
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(200)->getContent(), true)['data']['status']['id'];

        $this->assertEquals(self::STATUS_PROCESSED, $requestStatusId);
        $this->assertNotEquals(self::STATUS_HR_REVIEWED, $requestStatusId);
    }

    /** @test */
    public function completeRequestAsUser()
    {
        $this->put(
            'api/request/complete/' . $this->request->id,
            [],
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /** @test */
    public function completeRequestAsHrStaff()
    {
        $this->put(
            'api/request/complete/' . $this->request->id,
            [],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(403);
    }

    /** @test */
    public function getAllRequestStatus()
    {
        $this->get('api/request/status', ['authorization' => "bearer $this->token_user"])->assertStatus(200);
    }

    /** @test */
    public function getRequestsPdfFileAsUser()
    {
        $this->get('api/request/pdf', ['authorization' => "bearer $this->token_user"])->assertStatus(403);
    }

    /** @test */
    public function getRequestsPdfFileAsHrStaff()
    {
        $this->get('api/request/pdf', ['authorization' => "bearer $this->token_hr"])->assertStatus(200);
    }

    /** @test */
    public function getRequestsPdfFileAsManager()
    {
        $this->get('api/request/pdf', ['authorization' => "bearer $this->token_manager"])->assertStatus(200);
    }
}
