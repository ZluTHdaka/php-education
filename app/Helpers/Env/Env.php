<?php
declare(strict_types=1);

namespace App\Helpers\Env;

use App\Foundation\Application;

class Env
{
    protected static ?array $loaded_data = null;

    public static function get(string $key, mixed $default = null)
    {
        self::init();

        if (array_key_exists($key, self::$loaded_data)) {
            return self::$loaded_data[$key];
        } else {
            return $default;
        }
    }

    protected static function init(): void
    {
        /** @var Application $app */
        $app = Application::getInstance();

        if (is_null(self::$loaded_data)) {
            $handle = fopen($app->getRootPath() . DIRECTORY_SEPARATOR . '.env', "r");
            if ($handle) {
                self::$loaded_data = [];
                while (($line = fgets($handle)) !== false) {
                    $line = trim($line);
                    $exploded_line = explode('=', $line);
                    if (count($exploded_line) == 2) {
                        self::$loaded_data[$exploded_line[0]] = $exploded_line[1];
                    }
                }

                fclose($handle);
            }
        }
    }
}
