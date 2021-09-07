<?php

namespace Tests\Unit;

use App\Http\Resources\Status;
use App\Models\Request;
use App\Models\RequestLog;
use App\Models\RequestStatus;
use App\Models\User;
use App\Services\RequestService;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class RequestTest
 *
 * @package Tests\Unit
 */
class RequestTest extends TestCase
{
    use WithFaker;

    private const ID_TEST_USER = 1;
    private const ID_TEST_HR = 2;
    private const ID_TEST_MANAGER = 3;

    private $requestService;
    private $dataTestRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestService = new RequestService();
        $this->dataTestRequest = [
            'subject' => $this->faker->sentence,
            'description' => $this->faker->sentence(20),
        ];

        $this->withoutExceptionHandling();
    }

    /**
     * @test
     */
    public function testRequestUserRelation()
    {
        $user = new User();
        $user->name = 'test';

        $request = new Request();
        $request->user()->associate($user);

        $this->assertEquals('test', $request->user->name);
    }

    /**
     * @test
     */
    public function testRequestStatusRelation()
    {
        $status = new RequestStatus();
        $status->name = 'test';

        $request = new Request(['subject' => 'test', 'description' => 'test', 'user_id' => User::first()->id]);
        $request->status()->associate($status);

        $this->assertEquals($status->name, $request->status->name);

        $status->description = 'test';
        $status->save();
        $request->status_id = $status->id;
        $request->save();

        $this->assertEquals($request->id, $status->requests()->first()->id);
    }

    /**
     * @test
     */
    public function testRequestLogRelations()
    {
        $user = new User();
        $user->name = 'nameTest';

        $request = new Request();
        $request->subject = 'subjectTest';

        $log = new RequestLog();
        $log->message = 'test';
        $log->user()->associate($user);
        $log->request()->associate($request);

        $this->assertEquals($log->request->subject, $request->subject);
        $this->assertEquals($log->user->name, $user->name);
    }

    /**
     * @test
     */
    public function testRequestResourceData()
    {
        $request = new Request();
        $request->status_id = 2;
        $request->subject = 'testSubject';
        $request->description = 'testDescription';
        $request->user_id = 1;
        $request->save();

        $request->logs()->create(
            [
            'message' => 'testMessage',
            'user_id' => 1,
            ]
        );

        $resource = (new \App\Http\Resources\Request($request))->toArray(null);

        $this->assertEquals('testSubject', $resource['subject']);
        $this->assertEquals('testDescription', $resource['description']);
        $this->assertEquals(2, $resource['status']['id']);
        $this->assertEquals(1, $resource['created_by']['id']);
        $this->assertEquals('testMessage', $resource['logs'][0]['message']);
    }

    /**
     * @test
     */
    public function testRequestRoleResourceData()
    {
        $status = new RequestStatus();
        $status->name = 'testStatus';
        $status->color_code = '#';
        $status->description = 'testDescription';

        $resource = (new Status($status))->toArray(null);

        $this->assertEquals('testStatus', $resource['name']);
        $this->assertEquals('#', $resource['color_code']);
        $this->assertEquals('testDescription', $resource['description']);
    }

    /**
     * @test
     */
    public function testGetAllRequestForUser()
    {
        $requests = $this->requestService->getAll(User::find(self::ID_TEST_USER));

        foreach ($requests as $request) {
            $resource = $request->toArray(null);

            $this->assertEquals(1, $resource['created_by']['id']);
        }
    }

    /**
     * @test
     */
    public function testGetAllRequestForHrStaff()
    {
        $this->assertEquals($this->requestService->getAll(User::find(self::ID_TEST_HR))->count(), Request::count());
    }

    /**
     * @test
     */
    public function testGetAllRequestForManager()
    {
        $requests = $this->requestService->getAll(User::find(self::ID_TEST_MANAGER));

        foreach ($requests as $request) {
            $resource = $request->toArray(null);

            $this->assertEquals(RequestService::STATUS_HR_REVIEWED, $resource['status']['id']);
        }
    }

    /**
     * @test
     */
    public function testCreateRequestMethod()
    {
        $subject = $this->faker->name;
        $description = $this->faker->sentence;

        $request = $this->requestService->create(
            self::ID_TEST_USER, $subject, $description
        )->toArray(null);

        $this->assertEquals($subject, $request['subject']);
        $this->assertEquals($description, $request['description']);
        $this->assertEquals(self::ID_TEST_USER, $request['created_by']['id']);
        $this->assertEquals(RequestService::STATUS_OPEN, $request['status']['id']);
        $this->assertNotEmpty($request['logs']);
        $this->assertEquals(self::ID_TEST_USER, $request['logs'][0]['user_id']);
        $this->assertEquals(__('request.log_created'), $request['logs'][0]['message']);
    }

    /**
     * @test
     */
    public function testUpdatedRequestMethod()
    {
        $subject = $this->faker->name;
        $description = $this->faker->sentence;
        $request = Request::first();

        if ($request) {
            $requestUpdated = $this->requestService->update(
                $request, $subject, $description
            )->toArray(null);

            $this->assertEquals($subject, $requestUpdated['subject']);
            $this->assertEquals($description, $requestUpdated['description']);
            $this->assertEquals($request->user_id, $requestUpdated['created_by']['id']);
            $this->assertEquals($request->status_id, $requestUpdated['status']['id']);
            $this->assertNotEmpty($requestUpdated['logs']);
            $this->assertEquals(__('request.log_updated'), $requestUpdated['logs'][0]['message']);
            $this->assertEquals($request->user_id, $requestUpdated['logs'][0]['user_id']);
        }
    }

    /**
     * @test
     */
    public function testChangeStatusRequestMethod()
    {
        $request = Request::find(2);

        if ($request) {
            $requestUpdated = $this->requestService->updateStatus(
                $request,
                self::ID_TEST_HR,
                RequestService::STATUS_HR_REVIEWED
            )->toArray(null);

            $this->assertEquals($request->subject, $requestUpdated['subject']);
            $this->assertEquals($request->description, $requestUpdated['description']);
            $this->assertEquals($request->user_id, $requestUpdated['created_by']['id']);
            $this->assertEquals(RequestService::STATUS_HR_REVIEWED, $requestUpdated['status']['id']);
            $this->assertNotEmpty($requestUpdated['logs']);
            $this->assertEquals(self::ID_TEST_HR, $requestUpdated['logs'][0]['user_id']);
        }
    }

    /**
     * @test
     */
    public function testValidationDataRequest()
    {
        $validator = $this->requestService->validateRequestData($this->dataTestRequest);

        $this->assertNotTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataRequestWithoutData()
    {
        $validator = $this->requestService->validateRequestData([]);

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataRequestTooLongSubject()
    {
        $dataTest = $this->dataTestRequest;
        $dataTest['subject'] = $this->faker->sentence(100);

        $validator = $this->requestService->validateRequestData($dataTest);

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataRequestTooLongDescription()
    {
        $dataTest = $this->dataTestRequest;
        $dataTest['description'] = $this->faker->sentence(200);

        $validator = $this->requestService->validateRequestData($dataTest);

        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function testValidationDataRequestWithIntegerSubject()
    {
        $dataTest = $this->dataTestRequest;
        $dataTest['subject'] = $this->faker->randomNumber();

        $validator = $this->requestService->validateRequestData($dataTest);

        $this->assertTrue($validator->fails());
    }
}
