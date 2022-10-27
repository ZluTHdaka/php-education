<?php

namespace App\Foundation\HTTP\Exceptions;

use Exception;
use Throwable;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

class NotFoundException extends Exception
{
    public function __construct(string $message = "Not Found", int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}