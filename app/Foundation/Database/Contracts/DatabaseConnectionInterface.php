<?php
declare(strict_types=1);

namespace App\Foundation\Database\Contracts;

use PDO;

interface DatabaseConnectionInterface
{
    public function exec(string $statement): false|int;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param  array  $options
     */
    public function setOptions(array $options): void;

    /**
     * @return string
     */
    public function getDatabase(): string;

    /**
     * @param  string  $database
     */
    public function setDatabase(string $database): void;

    /**
     * @return string
     */
    public function getHost(): string;

    /**
     * @param  string  $host
     */
    public function setHost(string $host): void;

    /**
     * @return string
     */
    public function getPort(): string;

    /**
     * @param  string  $port
     */
    public function setPort(string $port): void;

    /**
     * @return string
     */
    public function getUsername(): string;

    /**
     * @param  string  $username
     */
    public function setUsername(string $username): void;

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @param  string  $password
     */
    public function setPassword(string $password): void;

    public function getConnection(): PDO;

    public function setConnection(PDO $connection): void;

    public function initConnection();

    public function getDSN(): string;
}
