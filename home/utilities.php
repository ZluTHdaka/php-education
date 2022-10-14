<?php

class EnvLoader
{
    protected string $env_path;
    protected array $env = [];
    protected bool $init = false;

    /**
     * @var EnvLoader[]
     */
    private static array $instances = [];

    final private function __construct()
    {
    }
    private function __clone()
    {
    }

    /**
     * @throws RuntimeException
     */
    final public function __wakeup()
    {
        throw new RuntimeException("Cannot unserialize a " . get_class(self::getInstance()));
    }

    final public static function getInstance(): EnvLoader
    {
        $cls = static::class;
        if (! isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public function init() : void
    {
        $this->env_path = dirname(__DIR__) . "/.env";
        $env_content = file_get_contents($this->env_path);
        $exploded_env_content = explode("\n", $env_content);
        foreach ($exploded_env_content as $env_line) {
            $exploded_env_line = explode("=", $env_line);
            if (count($exploded_env_line) === 2) {
                $this->env[$exploded_env_line[0]] = $exploded_env_line[1];
            }
        }

        $this->init = true;
    }

    public function get(string $key, mixed $default = null)
    {
        if (! $this->init) {
            $this->init();
        }

        return $this->env[$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    function dd(...$args): void
    {
        foreach ($args as $arg) {
            var_dump($arg);
        }

        exit(0);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return EnvLoader::getInstance()->get($key, $default);
    }
}
