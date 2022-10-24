<?php

require_once dirname(__DIR__)."/home/utilities.php";

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
$connect = $dsn."username=$username; password=$password";

