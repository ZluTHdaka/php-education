<?php

require './config/connection_config.php';

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
    protected string $query_format;
    protected array $selected_columns;
    protected array $query_args;
    protected array $exec_args;
    protected array $white_list;
    protected array $where_conditions = [];

    public function __construct(
        string $host = '127.0.0.1',
        string $port = '5432',
        string $username = 'php-education',
        string $password = 'php-education',
        string $database = 'php-education'
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
        $column_names = $this->connection->query(
                "SELECT column_name
                FROM information_schema.columns
                WHERE table_name = '$table' and table_schema = 'public'")
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($column_names as $columns) {
            $this->white_list['columns'][] = $columns['column_name'];
        }

        return $this;
    }

    public function select(array $columns): self
    {
        $this->selected_columns = $columns;
        $this->query_format = 'select %s from %s';
        $this->query_args = [
            implode(', ', $this->selected_columns),
            $this->current_table
        ];

        return $this;
    }

    public function where(string $column, string $operator, mixed $value, string $boolean = 'and'): self
    {
        if (count($this->where_conditions) === 0)
        {
            $boolean = '';
            $this->query_format .= ' where';
        }

        $this->where_conditions[] = [
            'boolean' => (string)$boolean, // or, and, &, |
            'column' => (string)$column,
            'operator' => (string)$operator,
            'value' => (string)$value,
        ];

        return $this;
    }

    public function insert(array $values): bool|array
    {
        $params = [];
        foreach($values as $key => $value) {
                $params[":".$key] = $value;
        }

        $this->selected_columns = array_keys($values);
        $this->query_format = 'insert into %s (%s) values(%s)';
        $this->query_args = [
            $this->current_table,
            implode(', ', $this->selected_columns),
            implode(', ', array_keys($params))
        ];
        $this->exec_args = $params;

        return $this->execute();
    }

    public function delete() : self
    {
        //...

        return $this;
    }

    protected function execute() : bool|array
    {
        try {

            if(count($this->where_conditions) !== 0)
            {
                foreach ($this->where_conditions as $condition)
                {
                    $this->query_format .= "%s (%s %s '%s') ";
                    foreach ($condition as $arg)
                    {
                        $this->query_args[] = $arg;
                    }
                }
            }

            $statement = $this->connection->prepare(
                vsprintf($this->query_format, $this->query_args)
            );

            if (isset($this->exec_args)) {
                $statement->execute($this->exec_args);
            } else {
                $statement->execute();
            }

            return $statement->fetchAll(PDO::FETCH_ASSOC);

        } catch (Throwable $exception) {
            dd($exception->getMessage());
        }
    }

    public function get(): bool|array
    {
        return $this->execute();
    }

    public function first(): mixed
    {
        $result = $this->get();

        if (count($result)) {
            return $result[0];
        }

        return $result;
    }
}

#DELETE TEST
//$query_delete = new QueryBuilder();
//$query_delete = $query_delete
//    ->table('articles')
//    ->delete()
//    ->where('id', '>', '4')
//    ->where('name', '!=', 'Моя вторая статья', 'and')
//    ->get()
//  ;

# INSERT TEST
$articles = [
    [
        'name' => 'Моя первая статья',
        'article' => 'Что-то интересное'
    ],
    [
        'name' => 'Моя вторая статья',
        'article' => 'Что-то интересное abc'
    ],
    [
        'name' => '123',
        'article' => 'Ну сработай пожалуйста'
    ],
];

$insert_builder = new QueryBuilder($host, $port, $database, $username, $password);
$insert_builder = $insert_builder
    ->table('articles');

foreach ($articles as $article) {
    $result = $insert_builder
        ->insert($article);
}

#SELECT TEST
$query_builder = new QueryBuilder();
$query_builder = $query_builder
    ->table('articles')
    ->select(['id', 'name', 'article']);

#WHERE TEST
$query_builder = $query_builder
    ->where('name', '=', '123')
    ->where('article', 'not like', '%abc%', 'or')
    ->where('id', '<', '10',);
#CLEAR_BUTTON
print_r(
    '<form action="./database/clear_DB.php" method="post">
    <input type="submit" name="clear_DB" value="Reset DataBase structure to default" />
</form>');

dd($query_builder->get());
