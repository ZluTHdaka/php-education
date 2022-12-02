<?php
declare(strict_types=1);

namespace App\Http\Resources\Article;

use App\Http\Resources\Common\SingleResource;
use App\Http\Resources\User\User;

/**
 * @property \App\Models\Article $resource
 */
class Article extends SingleResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'article' => $this->resource->article,
            'created_at' => $this->resource->created_at,
        ];
    }
}
