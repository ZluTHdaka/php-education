<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Foundation\Database\QueryBuilder;
use App\Foundation\HTTP\Request;
use App\Foundation\HTTP\Response;
use PDO;

class ArticleController
{
    public function getArticles(Request $request): Response
    {
        $db = require path('/config/database.php');
        $query = new QueryBuilder(...$db['connection']);
        $query->table('articles')->select();

        $page = $request->getQuery()['page'] ?? 1;
        $limit = $request->getQuery()['limit'] ?? 10;
        $get_operators = [];
        $page = (int)$page;
        $limit = (int)$limit;

        if (isset($request->getQuery()['operators'])) {
            $get_operators = explode('%', $request->getQuery()['operators']);
        }

        foreach ($query->white_list['columns'] as $arg) {
            if (isset($request->getQuery()[$arg])) {
                if (count($get_operators)) {
                    $query->where($arg, $get_operators[0], $_GET[$arg]);
                    array_shift($get_operators);
                } else {
                    $query->where($arg, '=', $request->getQuery()[$arg]);
                }
            }
        }

        $response = new Response();
        $data = $query->paginate($page, $limit);
        $meta = [
            'page' => 'page = '.$page,
            'limit' => 'limit = '.$limit,
            'count' => 'count = '.count($data)
        ];
        $prepared_data = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $prepared_meta = implode(' ', $meta);
        $response->setContent(sprintf('data : %s <br> meta : %s', $prepared_data,$prepared_meta));
        $response->setHeader("content-type", 'text/html');
        return ($response);
    }

    public function postArticles(Request $request) : Response
    {
        $db = require path('/config/database.php');
        $query = new QueryBuilder(...$db['connection']);
        $query->table('articles')->select();

        $page = (int)($request->getQuery()['page'] ?? 1);
        $limit = (int)($request->getQuery()['limit'] ?? 10);

        $get_content = $request->getBody();

        $filter = $get_content['filter'] ?? null;
        if (isset($filter))
        {
            foreach ($filter as $where_condition)
            {
                $query->where(...$where_condition);
            }
        }

        $response = new Response();
        $data = $query->paginate($page, $limit);
        $meta = [
            'page' => 'page = '.$page,
            'limit' => 'limit = '.$limit,
            'count' => 'count = '.count($data)
        ];
        $prepared_data = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $prepared_meta = implode(' ', $meta);
        $response->setContent(sprintf('data : %s <br> meta : %s', $prepared_data,$prepared_meta));
        $response->setHeader("content-type", 'text/html');
        return ($response);
    }

    public function clear(): void
    {

        $scriptfile = fopen(path('database/init.sql'), "rb");
        if (!$scriptfile) {
            die("ERROR: Couldn't open $scriptfile.\n");
        }

        // grab each line of file, skipping comments and blank lines

        $script = '';
        while (($line = fgets($scriptfile)) !== false) {
            $line = trim($line);
            if (preg_match("/^#|^--|^$/", $line)) {
                continue;
            }
            $script .= $line;
        }

        // explode script by semicolon and run each statement

        $statements = explode(';', $script);

//        $config = [
//            env('DB_HOST', '127.1.0.1'),
//            env('DB_PORT', '5432'),
//            env('DB_DATABASE', 'php-education'),
//            env('DB_USER', 'php-education'),
//            env('DB_PASSWORD', 'php-education')
//        ];

        $db = require(path('config/database.php'));

        $pdo = new PDO(
            vsprintf('pgsql:host=%s; port=%s; dbname=%s; user=%s; password=%s', $db['connection'])
        );

        foreach ($statements as $sql) {
            if ($sql === '') {
                continue;
            }
            $query = $pdo->query($sql);
            if ($query->errorCode() !== '00000') {
                header('Location: http://' . env('HOST', '127.0.0.1') .
                    ':' . env('PORT', '8001') . '/articles');
                die("ERROR: SQL error code: " . $query->errorCode() . "\n");
            }
        }

        header('Location: http://' . env('HOST', '127.0.0.1') .
            ':' . env('PORT', '8001') . '/articles');
    }

    public function fill(): void
    {
        $db = require path('config/database.php');
        $insertion = new QueryBuilder(...$db['connection']);
        $params = [];
        for ($iteration = 0; $iteration < 100; ++$iteration) {
            $params[] =
                [
                    'name' => 'Article №' . $iteration,
                    'article' => 'Today number is ' . $iteration
                ];
        }
        foreach ($params as $data) {
            $insertion->table('articles')->insert($data);
        }
        header("Location: http://" . env('HOST') . ':' . env('PORT') . '/articles');
    }
}