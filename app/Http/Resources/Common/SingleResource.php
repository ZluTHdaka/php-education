<?php
declare(strict_types=1);

namespace App\Http\Resources\Common;

use App\Models\Common\BaseModel;

abstract class SingleResource
{
    public function __construct(
        protected ?BaseModel $resource
    )
    {}

    public function toArray(): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        return is_array($this->resource)
            ? $this->resource
            : $this->resource->toArray();
    }

    public static function collection(?array $data): array
    {
        if (is_null($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $item) {
            $result[] = (new static($item))->toArray();
        }

        return $result;
    }
}
