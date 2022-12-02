<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Foundation\Database\QueryBuilder;
use App\Foundation\HTTP\Request;
use App\Foundation\HTTP\Response;
use App\Http\Controllers\Common\BaseCRUDController;
use App\Http\Resources\Article\ArticleCollection;
use App\Models\Article;

class ArticleController extends BaseCRUDController
{
    public function __construct()
    {
        $this->setCurrentModel(new Article());
        $this->single_resource = \App\Http\Resources\Article\Article::class;
        $this->collection_resource = ArticleCollection::class;
    }

    public function index(Request $request): Response
    {
        return $this->respond(
            $this->parentIndex(
                request: $request
            )
        );
    }

    public function show(Request $request, $key): Response
    {
        return $this->respond(
            $this->parentShow(
                request: $request,
                key: $key
            )
        );
    }

    public function store(Request $request): Response
    {
        return $this->respond(
            $this->parentStore(
                request: $request,
                closure: function (Article $model, string $mode) {
//                    if ($mode == 'after') {
//                        throw new \RuntimeException('Всё очень плохо, бегите от компьютера!');
//                    }
                }
            )
        );
    }

    public function updated(Request $request, $key): Response
    {
        return $this->respond(
            $this->parentUpdate(
                request: $request,
                key: $key,
            )
        );
    }

    public function destroy(Request $request, $key): Response
    {
        return $this->respond(
            $this->parentDestroy(
                request: $request,
                key: $key,
            )
        );
    }

    protected function getDefaultOrder(): array|string
    {
        return '-id';
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return Article::query()->select();
    }
}
