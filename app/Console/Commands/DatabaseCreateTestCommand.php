<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use PDO;
use PDOException;

class DatabaseCreateTestCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:create-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command creates a new test database';

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'db:create-test';

    /**
     * Execute the console command.
     */
    public function __invoke()
    {
        $database = env('DB_DATABASE', false) . '_test';

        if (! $database) {
            $this->info('Database not configured.');
            return;
        }

        try {
            $pdo = $this->getPDOConnection(env('DB_HOST'), env('DB_PORT'), env('DB_USERNAME'), env('DB_PASSWORD'));

            $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . $database . ' ;');

            Config::set('database.connections.mysql.database', $database);
            Artisan::call("migrate --database=mysql");

            $this->info(sprintf('Successfully created %s database', $database));
        } catch (PDOException $exception) {
            $this->error(sprintf('Failed to create %s database, %s', $database, $exception->getMessage()));
        }
    }

    /**
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     * @return PDO
     */
    private function getPDOConnection(string $host, int $port, string $username, string $password): PDO
    {
        return new PDO(sprintf('mysql:host=%s;port=%d;', $host, $port), $username, $password);
    }
}
