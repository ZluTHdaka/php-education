<?php
require_once 'global_helpers.php';
date_default_timezone_set("Europe/Moscow");

spl_autoload_register(function (string $className) {
    $className = str_replace('App\\', '', $className);
    require_once __DIR__ . '/../app/' . str_replace('\\', '/', $className) . '.php';
});

require_once '../app/Helpers/functions_helpers.php';

set_exception_handler([
    App\Foundation\Exception\ExceptionHandler::class,
    'handleException'
]);

set_error_handler([
    App\Foundation\Exception\ExceptionHandler::class,
    'handleError'
]);

$app = App\Foundation\Application::getInstance();

$app->setRootPath($_ENV['APP_BASE_PATH'] ?? dirname(__DIR__));

return $app;
