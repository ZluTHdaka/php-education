<?php

namespace App\Models;

use App\Models\Common\BaseModel;


class Comment extends BaseModel
{
    protected string $table = "comments";

    /** @var int */
    public $id;

    /** @var int */
    public $article_id;

    /** @var string */
    public $comment;

    /** @var string */
    public $created_at;
}