<?php
declare(strict_types=1);
require_once __DIR__.'/home/utilities.php';

$host = (string)env('HOST', '127.0.0.1');
$port = ':'.env('PORT', '1337');

try {
    exec('php -S ' . $host . $port);
} catch (\Throwable $exception){
    dd($exception);
}
// Должен запускать php -S с параметрами из env файла