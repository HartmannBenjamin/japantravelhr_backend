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
        $this->withoutExceptionHandling();

        $this->databaseService = new DatabaseService();
    }

    /**
     * @test
     */
    public function testDatabaseServiceFunctions()
    {
        $this->databaseService->createDatabase('test');

        $databaseExists = $this->databaseService->databaseExists('test');
        $this->assertTrue($databaseExists);

        $this->databaseService->dropDatabase('test');

        $databaseExists = $this->databaseService->databaseExists('test');
        $this->assertFalse($databaseExists);
    }

    /**
     * @test
     */
    public function testDropDatabaseArtisanCommand()
    {
        Artisan::call('db:drop');

        $database = env('DB_DATABASE', false);
        $databaseExists = $this->databaseService->databaseExists($database);
        $this->assertFalse($databaseExists);
    }

    /**
     * @test
     */
    public function testCreateDatabaseArtisanCommand()
    {
        Artisan::call('db:create');

        $database = env('DB_DATABASE', false);
        $databaseExists = $this->databaseService->databaseExists($database);
        $this->assertTrue($databaseExists);


    }

    /**
     * @test
     */
    public function testCreateDatabaseTestArtisanCommand()
    {
        Artisan::call('db:create-test');

        $database = env('DB_DATABASE', false) . '_test';
        $databaseExists = $this->databaseService->databaseExists($database);
        $this->assertTrue($databaseExists);
    }
}
