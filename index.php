<?php
require_once __DIR__.'/home/utilities.php';
require_once __DIR__.'/classes/QueryBuilder.php';

define('DEBUG', env('DEBUG', true));

if(DEBUG === 'true') {
    require_once __DIR__ . "/tests/test_unit.php";
}

function fill_db() : array
{
    $data = [];
    for ($iteration = 0; $iteration < 100; ++$iteration)
    {
        $data[] =
            [
                'name' => 'Article '.$iteration,
                'article' => 'Today number is '.$iteration
            ];
    }
    return $data;
}

$config = [
    env('DB_HOST', '127.1.0.1'),
    env('DB_PORT', '5432'),
    env('DB_DATABASE', 'php-education'),
    env('DB_USER', 'php-education'),
    env('DB_PASSWORD', 'php-education')
];

#CLEAR_BUTTON
/** @noinspection ForgottenDebugOutputInspection */
print_r(
    '<form action="/database/clear_DB.php" method="post">
        <input type="submit" name="clear_DB" value="Reset DataBase structure to default" />
    </form>'
);

#FILL_BUTTON
/** @noinspection ForgottenDebugOutputInspection */
print_r(
    '<form action="/index.php" method="post">
        <input type="submit" name="filling_button" value="Filling DataBase with 100 essences">
    </form>'
);


if (isset($_POST['filling_button'])) {
    $insertion = new QueryBuilder(...$config);
    foreach (fill_db() as $data)
    {
        $insertion->table('articles')->insert($data);
    }
    header("Location: http://".env('HOST').':'.env('PORT'));
}

$query = new QueryBuilder(...$config);

#SIMPLY GET
$query->table('articles')->select();

$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 10;
$output = [];
$get_operators = [];

if (isset($_GET['operators']))
{
    $get_operators = explode('%', $_GET['operators']);
}

foreach ($query->white_list['columns'] as $arg)
{
    if (isset($_GET[$arg]))
    {
        if (count($get_operators))
        {
            $query->where($arg, $get_operators[0], $_GET[$arg]);
            array_shift($get_operators);
        }
        else
        {
            $query->where($arg, '=', $_GET[$arg]);
        }
    }
}
dd($query->paginate($page, $limit));
