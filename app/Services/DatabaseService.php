<?php

namespace App\Services;

use PDO;

/**
 * Class DatabaseService
 *
 * @package App\Services
 */
class DatabaseService
{
    /**
     * @param $database
     */
    public function createDatabase($database)
    {
        $pdo = $this->getPDOConnection(env('DB_HOST'), env('DB_PORT'), env('DB_USERNAME'), env('DB_PASSWORD'));

        $pdo->exec('CREATE DATABASE IF NOT EXISTS ' . $database . ' ;');
    }

    /**
     * @param $database
     */
    public function dropDatabase($database)
    {
        $pdo = $this->getPDOConnection(env('DB_HOST'), env('DB_PORT'), env('DB_USERNAME'), env('DB_PASSWORD'));

        $pdo->exec('DROP DATABASE IF EXISTS ' . $database . ' ;');
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
