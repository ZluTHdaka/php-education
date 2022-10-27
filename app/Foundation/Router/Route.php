<?php
declare(strict_types=1);

namespace App\Foundation\Router;

use App\Foundation\HTTP\Enums\HTTPMethodsEnum;

class Route
{
    protected string $name;

    public function __construct(
        public HTTPMethodsEnum $method,
        public string $path,
        public string $pattern,
        public array $variables,
        public string $controller_class,
        public string $controller_method
    )
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     */
    public function name(string $name): void
    {
        $this->name = $name;
    }
}
