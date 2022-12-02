<?php
declare(strict_types=1);

namespace App\Http\Resources\Common;

use App\Foundation\Database\Paginator\Paginator;

abstract class CollectionResource
{
    protected ?string $single_resource;

    public function __construct(
        protected array|Paginator|null $collection
    )
    {}

    public function toArray(): array
    {
        if (is_array($this->collection)) {
            $result = [];
            foreach ($this->collection as $item) {
                $result[] = new $this->single_resource($item);
            }

            return [
                'data' => $result,
            ];
        }

        if ($this->collection instanceof Paginator) {
            $result = [];
            foreach ($this->collection->getData() as $item) {
                $result[] = (new $this->single_resource($item))->toArray();
            }

            return [
                'data' => $result,
                'meta' => $this->collection->getPaginationInfo(),
            ];
        }

        return [];
    }
}
