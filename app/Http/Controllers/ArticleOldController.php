<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Foundation\Database\QueryBuilder;
use App\Foundation\HTTP\Request;
use App\Foundation\HTTP\Response;
use PDO;

/**
 * @deprecated
 */
class ArticleOldController
{
    public function response(mixed $data, mixed $meta = null, array $headers = [], int $code = 200) : Response
    {
        $response = new Response();

        $response->setHeaders($headers);
        $response->setCode($code);

        $response_content = [
            'data' => $data
        ];

        if ($meta) {
            $response_content['meta'] = $meta;
        }

        try {
            $response->setBody(json_encode($response_content, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
        }

        return $response;
    }

    public function makeNotFoundResponse(): Response
    {
        return $this->response(
            data: 'Not found',
            code: 404
        );
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $query_builder = new QueryBuilder(...config('database.connection'));
        $query_builder->table('articles');

        return $query_builder;
    }

    public function store(Request $request): Response
    {
        $query = $this->getQueryBuilder();

        $get_content = $request->all();
        $query->insert($get_content);
        $data = $query->select()->where('id', '=', $query->lastInsertedId())->first();
        return $this->response($data);
    }

    public function show(Request $request, $article_id): Response
    {
        // todo получение сущности по ID
        $query = $this->getQueryBuilder();

        $data = $query->select()->where('id', '=', $article_id)->first();

        if ($data) {
            return $this->response($data);
        }

        return $this->makeNotFoundResponse();
    }

    public function update(Request $request, $article_id): Response
    {
        // todo изменение сущности по ID
        // Нет проверки на Not Found
        $query = $this->getQueryBuilder();

        $updated_content = $request->all();
        $query->where('id', '=', $article_id)->update($updated_content);

        $data = $query->select()->where('id', '=', $article_id)->first();

        if ($data) {
            return $this->response($data);
        }

        return $this->makeNotFoundResponse();
    }

    public function destroy(Request $request, $article_id): Response
    {
        $query = $this->getQueryBuilder();

        $query->where('id', '=', $article_id);
        $data = $query->delete();

        if ($data) {
            return $this->response("OK");
        }

        return $this->makeNotFoundResponse();
    }


    public function index(Request $request): Response
    {
        // todo должен возвращать коллекцию сущностей (список)
        $query = $this->getQueryBuilder();
        $query->select();
        $query_args = [];
        $page = (int)($request->get('page') ?? 1);
        $limit = (int)($request->get('limit') ?? 10);
        $get_operators = [];

        if ($request->get('operators') !== null) {
            $get_operators = explode('%', $request->get('operators'));
        }

        foreach ($query->white_list['columns'] as $arg) {
            if ($request->get($arg) !== null) {
                if (count($get_operators)) {
                        $query_args[$arg] = [
                            'column' => $arg,
                            'operator' => $get_operators[0],
                            'value' => $request->get($arg),
                        ];
                        array_shift($get_operators);
                } else {
                    $query_args[$arg] = [
                        'column' => $arg,
                        'operator' => '=',
                        'value' => $request->get($arg),
                    ];
                }
            }
        }

        foreach($query_args as $where_condition)
        {
            $query->where(...$where_condition);
        }

        $data = $query->paginate($page, $limit);
        $meta = [
            'page' => $page,
            'limit' => $limit,
            'count' => $query->getTableSize(),
            'last_page' => $query->getLastPage(),
        ];

        return $this->response($data, $meta);
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

        $db = config('database.connection');

        $pdo = new PDO(
            vsprintf('pgsql:host=%s; port=%s; dbname=%s; user=%s; password=%s', $db)
        );

        foreach ($statements as $sql) {
            if ($sql === '') {
                continue;
            }
            $query = $pdo->query($sql);
            if ($query->errorCode() !== '00000') {
                header('Location: http://' . env('HOST', '127.0.0.1') .
                    ':' . env('PORT', '80') . '/articles');
                die("ERROR: SQL error code: " . $query->errorCode() . "\n");
            }
        }
        header('Location: http://' . env('HOST', '127.0.0.1') .
            ':' . env('PORT', '80') . '/articles');
    }

    public function fill(): void
    {
        $insertion = $this->getQueryBuilder();
        $params = [];
        for ($iteration = 0; $iteration < 100; ++$iteration) {
            $params[] =
                [
                    'name' => 'Article №' . $iteration,
                    'article' => 'Today number is ' . random_int(0, 10000)
                ];
        }
        foreach ($params as $data) {
            $insertion->insert($data);
        }
        header("Location: http://" . env('HOST') . ':' . env('PORT') . '/articles');
    }
}
