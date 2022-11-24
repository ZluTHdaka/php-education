<?php
declare(strict_types=1);

namespace App\Foundation\Database;

use mysql_xdevapi\Exception;
use PDO;

class QueryBuilder
{
    protected PDO $connection;
    protected int $size = 0;
    protected int $last_page = 0;
    protected string $current_table = '';
    protected string $query_format = '';
    protected array $selected_columns = [];
    protected array $query_args = [];
    protected array $exec_args = [];
    protected array $where_conditions = [];
    public array $white_list = [];

    public function __construct(
        string $host,
        string $port,
        string $username,
        string $password,
        string $database
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
        $this->size = $this->connection->query(
            "SELECT count(*) FROM $table"
        )->fetchAll(PDO::FETCH_ASSOC)[0]['count'];

        return $this;
    }

    public function select(array $columns = ['*']): self
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
        }

        $this->where_conditions[] = [
            'boolean' => $boolean, // or, and, &, |
            'column' => $column,
            'operator' => $operator,
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
        $response = $this->execute();
        if ($response) {
            ++$this->size;
        }
        return $response;
    }

    /** @noinspection SqlWithoutWhere */
    public function delete() : self|bool
    {
        $this->query_format = 'delete from %s';
        $this->query_args[] = $this->current_table;
        $response = $this->execute();
        if ($response) {
            --$this->size;
            return $this;
        }
        return false;
    }

    public function update($values) : QueryBuilder
    {
        $params = [];
        foreach ($values as $key => $value)
        {
            $params[] = "$key = '$value'";
        }

        $this->query_format = 'update %s set %s' ;
        $this->query_args = [
            $this->current_table,
            implode(', ', $params)
        ];

        $this->execute();
        return $this;
    }

    protected function execute() : bool|array
    {
        try {
            if(count($this->where_conditions) !== 0)
            {
                $this->query_format .= ' where';
                foreach ($this->where_conditions as $condition)
                {
                    $this->query_format .= " %s (%s %s %s)";
                    foreach ($condition as $arg)
                    {
                        $this->query_args[] = $arg;
                    }
                }

            }

            $statement = $this->connection->prepare(
                vsprintf($this->query_format, $this->query_args)
            );

            $statement->execute($this->exec_args ?? null);
            $this->exec_args = [];

            $response = $statement->fetchAll(PDO::FETCH_ASSOC);
            if (count($response)) {
                return $response;
            }

            return false;

        } catch (\Throwable $exception) {
            $exception->getMessage();
            return false;
        }
    }

    public function get(): bool|array
    {
        return $this->execute();
    }

    public function lastInsertedId(): bool|int
    {
        return (int)$this->connection->lastInsertId();
    }

    public function limit(int $count = 10) : array|bool
    {
        $this->query_format .= ' limit %s';
        $this->query_args[] = $count;
        return $this->get();
    }

    public function paginate(int &$page, int $limit, $order = 1) : array|bool
    {
        switch($order) {
            case 1:
                $this->query_format .= ' order by id ASC';
                break;
            case 2:
                $this->query_format .= ' order by id DESC';
                break;
            default:
        }

        $result = $this->get();
        $count = count($result);
        $this->last_page = (int)(ceil($count / $limit));
        if ($page === 0) {
            $page = $this->last_page;
        }
        $selection_offset = ($page - 1) * $limit;
        $selection_limit = $limit;
        return array_slice($result, offset: $selection_offset, length: $selection_limit);
    }

    public function getLastPage() : int
    {
        return $this->last_page;
    }

    public function first($count = 1): array|null
    {
        $result = $this->get();

        if (is_array($result) && count($result)) {
            return $result[0];
        }

        return null;
    }

    public function last(): string|int|bool|array|null
    {
        $result = $this->get();

        if(count($result)){
            return array_key_last($result);
        }

        return $result;
    }

    public function getTableSize(): int
    {
        return $this->size;
    }
}
