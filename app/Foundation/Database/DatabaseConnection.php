<?php

namespace App\Foundation\Database;

use PDO;

class DatabaseConnection
{
    protected PDO $connection;

    public function __construct(
        protected string $database,
        protected string $host = 'localhost',
        protected string $port = '5432',
        protected string $username = 'postgres',
        protected string $password = 'postgres',
        protected array $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    )
    {
        $this->initConnection();
    }

    public function exec(string $statement): false|int
    {
        return $this->connection->exec($statement);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param  array  $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @param  string  $database
     */
    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param  string  $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @param  string  $port
     */
    public function setPort(string $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param  string  $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param  string  $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function setConnection(PDO $connection): void
    {
        $this->connection = $connection;
    }

    public function initConnection(): void
    {
        $this->connection = new PDO(
            $this->getDSN(),
            $this->getUsername(),
            $this->getPassword(),
            $this->getOptions(),
        );
    }

    public function getDSN(): string
    {
        return sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;',
            $this->getHost(),
            $this->getPort(),
            $this->getDatabase()
        );
    }
}
