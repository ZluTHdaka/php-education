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

    public function where(string $column, string $operator, mixed $value): self
    {
        // ...
        return $this;
    }

    public function insert(array $values): mixed
    {
        // ...
        return $this;
    }

    public function get(): mixed
    {
        return $this->execute();
    }
}

$articles = [
    [
        'name' => 'Моя первая статья',
        'article' => 'Что-то интересное',
    ],
    [
        'name' => 'Моя вторая статья',
        'article' => 'Что-то интересное abc',
    ],
];

$insert_builder = new QueryBuilder();
$insert_builder = $insert_builder
    ->table('articles')
;

foreach ($articles as $article) {
    $result = $insert_builder->insert($article);
}

$query_builder = new QueryBuilder();
$query_builder = $query_builder
    ->table('articles')
    ->select(['id', 'name'])
;

$query_builder = $query_builder
    ->where('name', '=', 'test1')
    ->where('article', 'ilike', '%abc%')
    ->where('created_at', '>=', '2022-09-26')
;

dd($query_builder->get());
