<?php
declare(strict_types=1);

namespace App\Foundation\Database\Contracts;

interface QueryBuilderInterface
{
    public function select(string|array $select): self;
    public function where(string|array $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): self;
    public function orderby(string|array $sort, string $order = null): self;
    public function limit(int $limit): self;
    public function skip(int $offset): self;

    public function toSql(): string;
    public function count(): int;
    public function get(): mixed;
    public function first(): mixed;
    public function getPrimaryKey(): string;
}
