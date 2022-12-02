<?php

namespace App\Common\Hydrate;

interface CanHydrateInterface
{
    public static function hydrateFromSingle(mixed $data): ?CanHydrateInterface;

    public static function hydrateFromCollection(mixed $data): array;
}