<?php

namespace App\Models\Common\Interface;


use App\Models\Common\BaseModel;

interface HasOwnerKey
{
    public function getOwnerId(): int;
}