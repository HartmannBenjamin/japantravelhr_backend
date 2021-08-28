<?php

namespace Tests\Unit;

use App\Models\Request;
use App\Models\RequestLog;
use App\Models\RequestStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void {
        parent::setUp();

        $this->withoutExceptionHandling();
    }

    /** @test */
    public function testRequestUserRelation()
    {
        $user = new User();
        $user->name = 'test';

        $request = new Request();
        $request->user = $user;

        $this->assertEquals('test', $request->user->name);
    }

    /** @test */
    public function testRequestStatusRelation()
    {
        $status = new RequestStatus();
        $status->name = 'test';

        $request = new Request();
        $request->status = $status;

        $this->assertEquals($status->name, $request->status->name);
    }


    /** @test */
    public function testRequestLogRelation()
    {
        $user = new User();
        $user->name = 'nameTest';

        $request = new Request();
        $request->subject = 'subjectTest';

        $log = new RequestLog();
        $log->message = 'test';
        $log->user = $user;
        $log->request = $request;

        $this->assertEquals($log->request->subject, $request->subject);
        $this->assertEquals($log->user->name, $user->name);
    }
}
