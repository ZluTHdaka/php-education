<?php

namespace App\Foundation\Database;

use App\Common\Hydrate\CanHydrateInterface;
use App\Foundation\Database\Contracts\QueryBuilderInterface;
use App\Foundation\Database\Paginator\Paginator;
use App\Foundation\HTTP\Exceptions\NotFoundException;
use PDO;

class QueryBuilder implements QueryBuilderInterface
{

    private array $fields = [];
    private array $conditions = [];
    private array $execute = [];
    private string $sort_order;
    private string $query;
    private int $limits;
    private int $offs;

    public function __construct(
        protected string               $table,
        protected string               $primary_key,
        protected PDO                  $connection,
        protected ?CanHydrateInterface $hydrate_model = null,
    )
    {
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primary_key;
    }

    public function makeClearClone(): QueryBuilderInterface
    {
        return new QueryBuilder($this->table, $this->primary_key, $this->connection, $this->hydrate_model);
    }

    public function select(string|array $select = ["*"]): self
    {
        if (is_array($select)) {
            $this->fields = $select;
        } else {
            $this->fields[] = $select;
        }
        return $this;
    }

    public function where(string|array $column, mixed $operator = null, mixed $value = null, string $boolean = 'AND'): self
    {
        /*
        IF COLUMN IS ARRAY
        */
        if (is_array(func_get_args()[0])) {
            foreach ($column as $key => $val) {
                $this->where($key, $val);;
            }
            return $this;
        }
        if (!$this->conditions) {
            $boolean = ' WHERE';
        }
        if (!array_key_exists("1", func_get_args()) and !array_key_exists("2", func_get_args())) {
            return $this;
        } else if (array_key_exists("1", func_get_args()) and array_key_exists("2", func_get_args())) {
            /*
            IS NOT NULL
            */
            if (func_get_args()[1] == "!=" and (func_get_args()[2] == null or func_get_args()[2] == 'null') and (!is_array($column))) {
                $operator = "IS NOT";
                $value = "NULL";
                $this->conditions[] = array($boolean, $column, $operator, $value);
                return $this;
            }
            /*
            VALUE IS ARRAY
            */
//            if (is_array($value)) {
//                foreach ($value as $val) {
//                    $this->execute[] = $val;
//                }
//                $value = str_repeat('?,', count($value) - 1) . '?';
//                $this->conditions[] = array($boolean, $column, $operator, "($value)");
//                return $this;
//            }
            $this->execute[] = $value;
            $this->conditions[] = array($boolean, $column, $operator, "?");
            return $this;
        } else if (array_key_exists("1", func_get_args()) and !array_key_exists("2", func_get_args())) {
            /*
            IS NULL
            */
            if ((func_get_args()[1] == null or func_get_args()[1] == 'null') and (!is_array($column))) {
                $operator = "IS";
                $value = "NULL";
                $this->conditions[] = array($boolean, $column, $operator, $value);
                return $this;
            }
            /*
            IF VALUE EMPTY (null)
            */
            if (array_key_exists("1", func_get_args()) and is_null($value) and !is_null($operator)) {
                $value = $operator;
                $operator = '=';
                $this->execute[] = $value;
                $this->conditions[] = array($boolean, $column, $operator, "?");
                return $this;
            }
            $this->execute[] = $value;
            $this->conditions[] = array($boolean, $column, $operator, "?");
            return $this;
        } else {
            return $this;
        }
    }

    public function whereIn(string|array $column, mixed $operator = "IN", mixed $value = null, string $boolean = 'AND'): self
    {
        if (!$this->conditions) {
            $boolean = ' WHERE';
        }

        foreach ($value as $val) {
            $this->execute[] = $val;
        }
        $operator = "IN";
        $value = str_repeat('?,', count($value) - 1) . '?';
        $this->conditions[] = array($boolean, $column, $operator, "($value)");
        return $this;
    }

    /**
     * @param string|array $sort параметр по которому сортируем
     * @param string|null $order может быть "ASC" или "DESC"
     */
    public function orderby(string|array $sort, string $order = null): self
    {
        if (array_key_exists("1", func_get_args())) {
            $this->sort_order = $sort . ' ' . strtoupper($order);
        } else {
            if (is_array($sort)) {
                $this->sort_order = implode(', ', $sort);
            } else {
                $this->sort_order = $sort;
            }
        }
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limits = $limit;
        return $this;
    }

    public function skip(int $offset): self
    {
        $this->offs = $offset;
        return $this;
    }

    public function count(): int
    {
        $sth = $this->connection->prepare($this->toSql());
        $sth->execute($this->execute);
        return $sth->rowCount();
    }

    public function toSql(): string
    {
//        'SELECT ' . implode(', ', $this->fields)
        return 'SELECT ' . (empty($this->fields) ? "*" : implode(', ', $this->fields))
            . ' FROM ' . $this->table
            . $this->buildWhere()
            . (empty($this->sort_order) ? "" : ' ORDER BY' . " " . $this->sort_order)
            . (isset($this->limits) ? ' LIMIT ' . $this->limits : "")
            . (empty($this->offs) ? "" : ' OFFSET ' . $this->offs);
    }

    private function buildWhere()
    {
        $text = [];
        foreach ($this->conditions as $val) {
            foreach ($val as $id) {
                $text[] = $id;
            }
        }
        $where = implode(" ", $text);
        return $where;
    }

    public function firstOrFail(): mixed
    {
        $result = $this->first();

        if (is_null($result)) {
            throw new NotFoundException();
        }

        return $result;
    }

    public function first(): mixed
    {
        $this->limits = 1;
        $sth = $this->connection->prepare($this->toSql());
        $sth->execute($this->execute);

        $result = $sth->fetch();

        if (!is_null($this->hydrate_model)) {
            $result = $this->hydrate_model::hydrateFromSingle($result);
        }

        return $result;
    }

    public function get(): mixed
    {
        $sth = $this->connection->prepare($this->toSql());
        $sth->execute($this->execute);

        $result = $sth->fetchAll();

        if (!is_null($this->hydrate_model)) {
            $result = $this->hydrate_model::hydrateFromCollection($result);
        }

        return $result;
    }

    public function insert(array $data): mixed
    {
        $this->toInsert($data);
        $sth = $this->connection->prepare($this->query);
        $sth->execute($this->execute);
        $row_id = $this->connection->lastInsertId();

        $query = $this->makeClearClone();

        return $query->where($query->getPrimaryKey(), $row_id)->first();
    }

    private function toInsert(array $data): string
    {
        foreach ($data as $key => $value) {
            if ($key == "id") {
                continue;
            }
            $this->fields[] = $key;
            $this->execute[] = $value;
        }

        $this->query = "INSERT INTO " . $this->table . " (" . implode(", ", $this->fields) . ")"
            . " VALUES " . "(" . str_repeat('?,', count($this->execute) - 1) . "?" . ")";

        return $this->query;
    }

    public function update(array $data, array $where): mixed
    {
        $this->where($where);
        $this->toUpdate($data);
        $sth = $this->connection->prepare($this->query);
        $sth->execute($this->execute);

        $query = $this->makeClearClone();

        return $query->select()->where($where)->first();
    }

    private function toUpdate(array $data): string
    {
        $text = "";
        foreach ($data as $key => $value) {
            if ($value == end($data)) {
                $text .= " {$key} = ? ";
            } else {
                $text .= " {$key} = ?, ";
            }

            $this->execute[] = $value;
        }

        $tmp=array_shift($this->execute);
        $this->execute[] = $tmp;
        $this->query = "UPDATE " . $this->table
            . " SET " . $text
            . $this->buildWhere();


        return $this->query;
    }

    public function delete(array $data): void
    {
        foreach ($data as $key => $value) {
            if ($key == "id") {
                $this->execute[] = $value;
            }
        }
        $this->query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $sth = $this->connection->prepare($this->query);
        $sth->execute($this->execute);
    }

    public function paginate($limit, $page): Paginator
    {
        $total = $this->count();
        $this->limit($limit);
        if ($page > 1) {
            $skip_lines = $limit * ($page - 1);
            $this->skip($skip_lines);
        }

        $sth = $this->connection->prepare($this->toSql());
        $sth->execute($this->execute);
        $result = $sth->fetchAll();


        if (!is_null($this->hydrate_model)) {
            $result = $this->hydrate_model::hydrateFromCollection($result);
        }

        return new Paginator($result, $limit, $page, $total);
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commitTransaction(): void
    {
        $this->connection->commit();
    }

    public function rollBackTransaction(): void
    {
        $this->connection->rollBack();
    }

}
