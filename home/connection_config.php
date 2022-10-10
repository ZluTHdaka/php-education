<?php
$host = '127.0.0.1';
$port = '5432';
$database = 'php-education';
$dsn = sprintf(
    "pgsql:host=%s; port=%s; dbname=%s;",
    $host, $port, $database
);
$username = 'php-education';
$password = 'php-education';
$php_port = '8090';
