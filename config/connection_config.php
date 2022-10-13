<?php

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $searchfor = "/^.$key.*\$/m";
        $env = file_get_contents(realpath(__DIR__."/../\.env"));
        if(!$env) {die("ERROR: Couldn't open $searchfor.\n");}
        if (preg_match($searchfor, $env, $match))
        {
            $result = explode('=', $match[0])[1];
            return  $result;
        } else {
            return $default;
        }
    }
    // Должна возвращать значение переданного ключа, например HOST, из файла .env, лежащего в корне проекта
    // Иначе - значение переданное в переменную default
    // Например: env('DB_HOST', '127.0.0.2') вернёт значение в .env файле, если оно там есть, иначе вернёт 127.0.0.2
}

$host = env("HOST",'127.0.0.1');
$port = env('DB_PORT','5432');
$database = env('DB_DATABASE','php-education');
$username = env("DB_USERNAME",'php-education');
$password = env('DB_PASSWORD','php-education');
$php_port = env("PORT",'8001');
$dsn = sprintf(
    "pgsql:host=%s; port=%s; dbname=%s;",
    $host, $port, $database
);
