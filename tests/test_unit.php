<?php

require_once dirname(__DIR__).'/home/utilities.php';
require_once dirname(__DIR__).'/classes/QueryBuilder.php';
#DELETE TEST
$query_delete = new QueryBuilder
(
    host: env('DB_HOST', 'localhost'),
    port: env('DB_PORT', 5432),
    username: env('DB_USERNAME', 'php-education'),
    password: env('DB_PASSWORD', 'php-education'),
    database: env('DB_DATABASE', 'php-education')
);
$query_delete = $query_delete
    ->table('articles')
    ->where('id', '>', '10')
    ->where('name', '!=', 'Моя вторая статья')
    ->delete();

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

$insert_builder = new QueryBuilder(
    host: env('DB_HOST', 'localhost'),
    port: env('DB_PORT', 5432),
    username: env('DB_USERNAME', 'php-education'),
    password: env('DB_PASSWORD', 'php-education'),
    database: env('DB_DATABASE', 'php-education')
);

$insert_builder = $insert_builder
    ->table('articles');

foreach ($articles as $article) {
    $result = $insert_builder
        ->insert($article);
}

#SELECT TEST
$query_builder = new QueryBuilder(
    host: env('DB_HOST', 'localhost'),
    port: env('DB_PORT', 5432),
    username: env('DB_USERNAME', 'php-education'),
    password: env('DB_PASSWORD', 'php-education'),
    database: env('DB_DATABASE', 'php-education')
);
$query_builder = $query_builder
    ->table('articles')
    ->select(['id', 'name', 'article']);

#WHERE TEST
$query_builder = $query_builder
    ->where('name', '=', '123')
    ->where('article', 'not like', '%abc%', 'or')
    ->where('id', '<', '10');

#CLEAR_BUTTON
/** @noinspection ForgottenDebugOutputInspection */
print_r(
    '<form action="../database/clear_DB.php" method="post">
    <input type="submit" name="clear_DB" value="Reset DataBase structure to default" />
</form>');

$first_essence = clone $query_builder;
$last_essence = clone $query_builder;

/** @noinspection ForgottenDebugOutputInspection */
dd($query_builder->get(), $first_essence->first(), $last_essence->last());
