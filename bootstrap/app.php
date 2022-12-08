<?php
date_default_timezone_set("Europe/Moscow");

set_exception_handler([
    \App\Foundation\Exception\ExceptionHandler::class,
    'handleException'
]);

set_error_handler([
    \App\Foundation\Exception\ExceptionHandler::class,
    'handleError'
]);

$app = \App\Foundation\Application::getInstance();

$app->setRootPath($_ENV['APP_BASE_PATH'] ?? dirname(__DIR__));

return $app;
