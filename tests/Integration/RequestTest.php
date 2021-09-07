<?php

namespace Tests\Integration;

use App\Models\Request;
use App\Models\User;
use App\Services\RequestService;
use App\Services\UserService;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class RequestTest
 *
 * @package Tests\Integration
 */
class RequestTest extends TestCase
{
    use WithFaker;

    private $user;
    private $token_user;
    private $token_hr;
    private $token_manager;
    private $request;
    private $dataRequest = [
        'subject' => 'This is a test',
        'description' => 'Here\'s the description'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role_id' => UserService::ROLE_USER]);
        $this->token_user = JWTAuth::fromUser($this->user);

        $hr = User::factory()->create(['role_id' => UserService::ROLE_HR]);
        $this->token_hr = JWTAuth::fromUser($hr);

        $manager = User::factory()->create(['role_id' => UserService::ROLE_MANAGER]);
        $this->token_manager = JWTAuth::fromUser($manager);

        $this->request = Request::factory()->create(['status_id' => RequestService::STATUS_OPEN]);

        $this->withoutExceptionHandling();
    }

    /**
     * @test
     */
    public function testToGetAllRequest()
    {
        $this->get('request/all', ['authorization' => "bearer $this->token_user"])->assertStatus(200);
    }

    /**
     * @test
     */
    public function createRequestWithCommonData()
    {
        $request = json_decode(
            $this->post(
                'request/create',
                $this->dataRequest,
                ['authorization' => "bearer $this->token_user"]
            )->assertStatus(201)->getContent(), true
        )['data'];

        $this->assertEquals(
            [$this->dataRequest['subject'], $this->dataRequest['description']],
            [$request['subject'], $request['description']]
        );

        $this->assertEquals(RequestService::STATUS_OPEN, $request['status']['id']);
    }

    /**
     * @test
     */
    public function tryToCreateRequestWithoutData()
    {
        $this->post('request/create', [], ['authorization' => "bearer $this->token_user"])
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function tryToCreateRequestAsHrStaff()
    {
        $this->post(
            'request/create',
            $this->dataRequest,
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function tryToCreateRequestAsManager()
    {
        $this->post(
            'request/create',
            $this->dataRequest,
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function getUserRequestById()
    {
        $userToken = JWTAuth::fromUser($this->request->user);

        $this->get('request/get/' . $this->request->id, ['authorization' => "bearer $userToken"])
            ->assertStatus(200);
    }

    /**
     * @test
     */
    public function getRequestByIdAsOtherUser()
    {
        $this->get('request/get/' . $this->request->id, ['authorization' => "bearer $this->token_user"])
            ->assertStatus(403);
    }

    /**
     * @test
     */
    public function getRequestByIdAsHrStaff()
    {
        $this->get('request/get/' . $this->request->id, ['authorization' => "bearer $this->token_hr"])
            ->assertStatus(200);
    }

    /**
     * @test
     */
    public function getRequestByIdAsManagerStaff()
    {
        $this->get('request/get/' . $this->request->id, ['authorization' => "bearer $this->token_manager"])
            ->assertStatus(200);
    }

    /**
     * @test
     */
    public function createRequestAndVerifyInformation()
    {
        $requestId = json_decode(
            $this->post(
                'request/create',
                $this->dataRequest,
                ['authorization' => "bearer $this->token_user"]
            )->assertStatus(201)->getContent(), true
        )['data']['id'];

        $request = json_decode(
            $this->get(
                'request/get/' . $requestId,
                ['authorization' => "bearer $this->token_user"]
            )->assertStatus(200)->getContent(), true
        )['data'];

        $this->assertEquals(
            [$this->dataRequest['subject'], $this->dataRequest['description']],
            [$request['subject'], $request['description']]
        );
    }

    /**
     * @test
     */
    public function createRequestAndUpdateIt()
    {
        // creation of request
        $requestId = json_decode(
            $this->post(
                'request/create',
                $this->dataRequest,
                ['authorization' => "bearer $this->token_user"]
            )->assertStatus(201)->getContent(), true
        )['data']['id'];

        $attributesUpdated = [
            'subject' => 'This is a test updated',
            'description' => 'Here\'s the description updated'
        ];

        // update data of request
        $request = json_decode(
            $this->put(
                'request/edit/' . $requestId,
                $attributesUpdated,
                ['authorization' => "bearer $this->token_user"]
            )->assertStatus(200)->getContent(), true
        )['data'];

        // verify data is updated
        $this->assertNotEquals(
            [$this->dataRequest['subject'], $this->dataRequest['description']],
            [$request['subject'], $request['description']]
        );

        // verify data is correct
        $this->assertEquals(
            [$attributesUpdated['subject'], $attributesUpdated['description']],
            [$request['subject'], $request['description']]
        );
    }

    /**
     * @test
     */
    public function tryToUpdateRequestWithoutData()
    {
        $creatorToken = JWTAuth::fromUser(User::find($this->request->user_id));

        $this->put(
            'request/edit/' . $this->request->id,
            [],
            ['authorization' => "bearer $creatorToken"]
        )->assertStatus(404);
    }

    /**
     * @test
     */
    public function tryToUpdateRequestAsOtherUser()
    {
        $this->put(
            'request/edit/' . $this->request->id,
            $this->dataRequest,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function tryToUpdateARequestAsHrStaff()
    {
        $this->put(
            'request/edit/' . $this->request->id,
            $this->dataRequest,
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function tryToUpdateRequestAsManager()
    {
        $this->put(
            'request/edit/' . $this->request->id,
            $this->dataRequest,
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function tryToUpdateProcessedRequest()
    {
        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'status_id' => RequestService::STATUS_PROCESSED
        ]);

        $this->put(
            'request/edit/' . $request->id,
            $this->dataRequest,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function tryToUpdateHrReviewedRequest()
    {
        $request = Request::factory()->create([
            'user_id' => $this->user->id,
            'status_id' => RequestService::STATUS_HR_REVIEWED
        ]);

        $this->put(
            'request/edit/' . $request->id,
            $this->dataRequest,
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function changeRequestStatusAsHrStaff()
    {
        foreach (RequestService::ALL_STATUS as $statusId) {
            $requestStatusId = json_decode(
                $this->put(
                    'request/changeStatus/' . $this->request->id,
                    ['status_id' => $statusId],
                    ['authorization' => "bearer $this->token_hr"]
                )->assertStatus(200)->getContent(), true
            )['data']['status']['id'];

            $this->assertEquals($statusId, $requestStatusId);
        }
    }

    /**
     * @test
     */
    public function changeRequestStatusPassingNonExistentStatusId()
    {
        $this->put(
            'request/changeStatus/' . $this->request->id,
            ['status_id' => 6],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(404);
    }

    /**
     * @test
     */
    public function changeRequestStatusAsUser()
    {
        $this->put(
            'request/changeStatus/' . $this->request->id,
            ['status_id' => RequestService::STATUS_PROCESSED],
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function changeRequestStatusAsManager()
    {
        $this->put(
            'request/changeStatus/' . $this->request->id,
            ['status_id' => RequestService::STATUS_PROCESSED],
            ['authorization' => "bearer $this->token_manager"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function completeRequestAsManager()
    {
        // update request status
        $this->request->status_id = RequestService::STATUS_HR_REVIEWED;
        $this->request->save();

        $requestStatusId = json_decode(
            $this->put(
                'request/complete/' . $this->request->id,
                [],
                ['authorization' => "bearer $this->token_manager"]
            )->assertStatus(200)->getContent(), true
        )['data']['status']['id'];

        $this->assertEquals(RequestService::STATUS_PROCESSED, $requestStatusId);
    }

    /**
     * @test
     */
    public function completeRequestAsUser()
    {
        $this->put(
            'request/complete/' . $this->request->id,
            [],
            ['authorization' => "bearer $this->token_user"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function completeRequestAsHrStaff()
    {
        $this->put(
            'request/complete/' . $this->request->id,
            [],
            ['authorization' => "bearer $this->token_hr"]
        )->assertStatus(403);
    }

    /**
     * @test
     */
    public function getAllRequestStatus()
    {
        $this->get('request/status', ['authorization' => "bearer $this->token_user"])->assertStatus(200);
    }

    /**
     * @test
     */
    public function getRequestsPdfFileAsUser()
    {
        $this->get('request/pdf', ['authorization' => "bearer $this->token_user"])->assertStatus(403);
    }

    /**
     * @test
     */
    public function getRequestsPdfFileAsHrStaff()
    {
        $this->get('request/pdf', ['authorization' => "bearer $this->token_hr"])->assertStatus(200);
    }

    /**
     * @test
     */
    public function getRequestsPdfFileAsManager()
    {
        $this->get('request/pdf', ['authorization' => "bearer $this->token_manager"])->assertStatus(200);
    }
}
