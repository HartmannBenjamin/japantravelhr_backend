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
     * @param $database
     *
     * @return bool
     */
    public function databaseExists($database): bool
    {
        $pdo = $this->getPDOConnection(env('DB_HOST'), env('DB_PORT'), env('DB_USERNAME'), env('DB_PASSWORD'));

        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =:dbname");
        $stmt->execute(array(":dbname"=>$database));

        return $stmt->rowCount() == 1;
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
