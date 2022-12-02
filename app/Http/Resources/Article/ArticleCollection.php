<?php
declare(strict_types=1);

namespace App\Http\Resources\Article;

use App\Foundation\Database\Paginator\Paginator;
use App\Http\Resources\Common\CollectionResource;

/**
 * @property Paginator $collection
 */
class ArticleCollection extends CollectionResource
{
    protected ?string $single_resource = Article::class;
}
