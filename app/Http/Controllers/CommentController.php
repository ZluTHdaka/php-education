<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Foundation\Database\QueryBuilder;
use App\Foundation\HTTP\Request;
use App\Foundation\HTTP\Response;
use App\Http\Controllers\Common\BaseCRUDController;
use App\Http\Resources\Comment\CommentCollection;
use App\Models\Comment;

class CommentController extends BaseCRUDController
{
    public function __construct()
    {
        $this->setCurrentModel(new Comment());
        $this->single_resource = \App\Http\Resources\Comment\Comment::class;
        $this->collection_resource = CommentCollection::class;
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
            )
        );
    }

    public function update(Request $request, $key): Response
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
        return Comment::query()->select();
    }
}
