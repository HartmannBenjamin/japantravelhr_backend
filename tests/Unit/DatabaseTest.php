<?php

namespace Tests\Unit;

use App\Services\DatabaseService;
use Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Class DatabaseTest
 *
 * @package Tests\Unit
 */
class DatabaseTest extends TestCase
{
    use WithFaker;

    /**
     * @var DatabaseService
     */
    private $databaseService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseService = new DatabaseService();

        $this->withoutExceptionHandling();
    }

    /**
     * @test
     */
    public function testDatabaseServiceFunctions()
    {
        $this->databaseService->createDatabase('test');
        $this->assertTrue($this->databaseService->databaseExists('test'));

        $this->databaseService->dropDatabase('test');
        $this->assertFalse($this->databaseService->databaseExists('test'));
    }

    /**
     * @test
     */
    public function testDropDatabaseArtisanCommand()
    {
        Artisan::call('db:drop');

        $database = env('DB_DATABASE', false);
        $this->assertFalse($this->databaseService->databaseExists($database));
    }

    /**
     * @test
     */
    public function testCreateDatabaseArtisanCommand()
    {
        Artisan::call('db:create');

        $database = env('DB_DATABASE', false);
        $this->assertTrue($this->databaseService->databaseExists($database));
    }
}
