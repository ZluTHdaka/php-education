<?php

namespace App\Http\Resources\Comment;

use App\Http\Resources\Common\CollectionResource;
use App\Foundation\Database\Paginator\Paginator;

class CommentCollection extends CollectionResource
{
    protected ?string $single_resource = Comment::class;
}