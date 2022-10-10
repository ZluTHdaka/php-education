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
    protected string $query_format;
    protected array $selected_columns;
    protected array $query_args;
    protected array $exec_args;
    protected array $white_list;

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
                WHERE table_name = '{$table}' and table_schema = 'public'")
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

    public function where(array $data): self
    {
        //...


        return $this;
    }

    public function insert(array $values): self
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

        return $this;
    }

    public function delete() : self
    {
        //...

        return $this;
    }

    protected function execute() : mixed
    {
        try {
            $statement = $this->connection->prepare(
                vsprintf($this->query_format, $this->query_args)
            );

            (isset($this->exec_args) === true) ?
                $statement->execute($this->exec_args) :
                $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);

        } catch (\Throwable $exception) {
            dd($exception->getMessage());
        }
    }

    public function get(): mixed
    {
        return $this->execute();
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
];

$insert_builder = new QueryBuilder();
$insert_builder = $insert_builder
    ->table('articles');

foreach ($articles as $article) {
    $result = $insert_builder
        ->insert($article)
        ->get()
    ;
}

#SELECT TEST
$query_builder = new QueryBuilder();
$query_builder = $query_builder
    ->table('articles')
    ->select(['id', 'name', 'article']);

#WHERE TEST
$conditions = [
    [
        'name' => 'Моя первая статья',
        'operator' => '='
    ],
    [
        'article' => '%abc%',
        'operator' => 'ilike',
        'merge' => 'or'
    ],
    [
        'created_at' => '2022-09-26',
        'operator' => '>=',
        'merge' => 'and'
    ]
//    'data' => [
//        'name' => 'Моя первая статья',
//        'article' => '%abc%',
//        'created_at' => '2022-09-26'
//    ],
//    'operator' => [
//        '=',
//        'ilike',
//        '>='
//    ],
//    'merge' => [
//        'or',
//        'and'
//    ]
];

foreach ($conditions as $data_parameters) {
    $query_builder = $query_builder
        ->where($data_parameters);
}

#CLEAR_BUTTON
print_r(
'<form action="./home/clear_DB.php" method="post">
    <input type="submit" name="clear_DB" value="Reset DataBase structure to default" />
</form>');

dd($query_builder->get());