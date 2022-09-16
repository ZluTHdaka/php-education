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
    public function __construct()
    {

    }

    public function select(): string
    {
        return "test";
    }
}

$query_builder = new QueryBuilder();

dd([
    'hello' => 'world'
]);
