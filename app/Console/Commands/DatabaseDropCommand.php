<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use PDOException;

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
            $pdo = $this->getPDOConnection(env('DB_HOST'), env('DB_PORT'), env('DB_USERNAME'), env('DB_PASSWORD'));

            $pdo->exec('DROP DATABASE IF EXISTS ' . $database . ' ;');

            $this->info(sprintf('Successfully drop %s database', $database));
        } catch (PDOException $exception) {
            $this->error(sprintf('Failed to drop %s database, %s', $database, $exception->getMessage()));
        }
    }

    /**
     * @param string  $host
     * @param integer $port
     * @param string  $username
     * @param string  $password
     *
     * @return PDO
     */
    private function getPDOConnection(string $host, int $port, string $username, string $password): PDO
    {
        return new PDO(sprintf('mysql:host=%s;port=%d;', $host, $port), $username, $password);
    }
}
