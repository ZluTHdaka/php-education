<?php

namespace App\Foundation\Configuration;

use App\Common\Patterns\Singleton;

/**
 * @method static Config getInstance();
 */
class Config extends Singleton
{
    protected array $current_configuration;

    public function init(): void
    {
        $config_path = path('config');
        $config_files = scandir($config_path);
        foreach ($config_files as $config_file_name) {
            if (str_ends_with($config_file_name, '.php')) {
                $config_name = explode('.', $config_file_name)[0];
                $this->current_configuration[$config_name] = require $config_path . DIRECTORY_SEPARATOR . $config_file_name;
            }
        }
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if (! count($this->current_configuration)) {
            $this->init();
        }

        return helper_array_get($this->current_configuration, $name, $default);
    }
}