<?php

if (!function_exists('dd')) {
    function dd(...$args): void
    {
        foreach ($args as $arg) {
            var_dump($arg);
        }

        exit(0);
    }
}


class QueryBuilder
{
    protected PDO $connection;
    protected string $current_table;
    protected array $selected_columns;

    public function __construct(
        string $host = '127.0.0.1',
        string $port = '5432',
        string $username = 'php-education',
        string $password = 'php-education',
        string $database = 'php-education',
    )
    {
        $this->connection = new PDO(
            sprintf(
                "pgsql:host=%s; port=%s; dbname=%s; user=%s; password=%s",
                $host, $port, $database, $username, $password
            )
        );
    }

    public function table(string $table): self
    {
        $this->current_table = $table;

        return $this;
    }

    protected function execute()
    {
        try {
            $query = sprintf(
                "SELECT %s FROM %s",
                implode(', ', $this->selected_columns),
                $this->current_table,
            );

            $statement = $this->connection->prepare(
                $query
            );
            $statement->execute();
            $selected_data = $statement->fetchAll(PDO::FETCH_ASSOC);

            return $selected_data;
        } catch (\Throwable $exception) {
            dd($exception->getMessage());
        }
    }

    public function select(array $columns): self
    {
        $this->selected_columns = $columns;

        return $this;
    }

    public function get(): mixed
    {
        return $this->execute();
    }
}

$query_builder = new QueryBuilder();
dd($query_builder->table('articles')->select(['id', 'name'])->get());