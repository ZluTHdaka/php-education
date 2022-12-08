<?php
declare(strict_types=1);

namespace App\Http\Resources\Article;

use App\Http\Resources\Common\CollectionResource;

class ArticleCollection extends CollectionResource
{
    protected ?string $single_resource = Article::class;
}
