<?php

namespace App\Console\Commands;

use App\Services\DatabaseService;
use Illuminate\Console\Command;
use PDOException;

/**
 * Class DatabaseDropCommand
 *
 * @package App\Console\Commands
 */
class DatabaseDropCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:drop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command drops database';

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'db:drop';

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
            (new DatabaseService())->dropDatabase($database);

            $this->info(sprintf('Successfully drop %s database', $database));
        } catch (PDOException $exception) {
            $this->error(sprintf('Failed to drop %s database, %s', $database, $exception->getMessage()));
        }
    }
}
