<?php

namespace App\Http\Resources\Comment;

use App\Http\Resources\Common\SingleResource;

/**
 * @property \App\Models\Comment $resource
 */
class Comment extends SingleResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->resource->id,
            'article_id' => $this->resource->article_id,
            'comment' => $this->resource->comment,
            'created_at' => $this->resource->created_at,
        ];
    }
}
