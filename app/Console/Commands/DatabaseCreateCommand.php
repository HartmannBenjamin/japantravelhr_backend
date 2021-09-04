<?php

namespace App\Console\Commands;

use App\Services\DatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use PDOException;

/**
 * Class DatabaseCreateCommand
 *
 * @package App\Console\Commands
 */
class DatabaseCreateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command creates a new database';

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'db:create';

    /**
     * Execute the console command.
     */
    public function __invoke()
    {
        $database = env('DB_DATABASE', false);

        if (! $database) {
            $this->info('Database not configured.');
            return;
        }

        try {
            (new DatabaseService())->createDatabase($database);

            Artisan::call("migrate");

            $this->info(sprintf('Successfully created %s database', $database));
        } catch (PDOException $exception) {
            $this->error(sprintf('Failed to create %s database, %s', $database, $exception->getMessage()));
        }
    }
}
