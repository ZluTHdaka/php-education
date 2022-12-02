<?php
namespace App\Models;

use App\Foundation\HTTP\Request;
use App\Models\Common\BaseModel;
use App\Models\Common\Interface\HasOwnerKey;

class Article extends BaseModel
{
    protected string $table = "articles";

    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $article;

    /** @var string */
    public $created_at;
}
