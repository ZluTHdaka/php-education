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
    protected string $current_action;
    protected array $selected_columns;
    protected string $query = '';

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

    protected function build_query(... $values) : void
    {
        switch($this->current_action)
        {
            case 'insert':
                $this->query = sprintf('insert into %s (%s) values (%s)',
                    $this->current_table,
                    implode(', ', $this->selected_columns),
                    "'".implode("', '", $values[0])."'"
                );
                break;

            case 'select':
                $this->query = sprintf('select %s from %s',
                    implode(', ', $this->selected_columns),
                    $this->current_table
                );
                break;

            case 'delete':
                $this->query = sprintf('delete from %s', $this->current_table);
                break;
            case 'where':
                if($this->query)
                {
                    $this->query .= sprintf(" %s %s %s'%s'",
                        $values[3],
                        $values[0],
                        $values[1],
                        $values[2]);
                }
        }
    }

    protected function execute()
    {
        try {
            $statement = $this->connection->prepare(
                $this->query
            );

            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);

        } catch (\Throwable $exception) {
            dd($exception->getMessage());
        }
    }

    public function select(array $columns): self
    {
        $this->selected_columns = $columns;
        $this->current_action = 'select';
        $this->build_query();

        return $this;
    }

    public function where(string $column, string $operator, mixed $value, string $logic = 'where'): self
    {
        //...
        $this->current_action = 'where';
        $this->build_query($column, $operator, $value, $logic);

        return $this;
    }

    public function insert(array $values): self
    {
        //...
        $this->selected_columns = array_keys($values);
        $this->current_action = 'insert';
        $this->build_query($values);

        return $this;
    }

    public function delete() : self
    {
        //...
        $this->current_action = 'delete';
        $this->build_query();
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
        'article' => 'Что-то интересное'
    ],
    [
        'name' => 'Моя вторая статья',
        'article' => 'Что-то интересное abc'
    ],
];

$insert_builder = new QueryBuilder();
$insert_builder = $insert_builder
    ->table('articles');

foreach ($articles as $article) {
    $result = $insert_builder->insert($article)->get();
}

$query_delete = new QueryBuilder();
$query_delete = $query_delete
    ->table('articles')
    ->delete()
    ->where('id', '>', '4')
    ->where('name', '!=', 'Моя вторая статья', 'and')
    ->get();

$query_builder = new QueryBuilder();
$query_builder = $query_builder
    ->table('articles')
    ->select(['id', 'created_at', 'name', 'article']);

$query_builder = $query_builder
    ->where('name', '=', 'Моя первая статья')
    ->where('article', 'ilike', '%abc%', 'or')
    ->where('created_at', '>=', '2022-09-26', 'or');

dd($query_builder->get());
