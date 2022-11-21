<?php

use App\Foundation\Router\Router;

Router::get('/articles/clear', 'App\Http\Controllers\ArticleController@clear');
Router::get('/articles/fill', 'App\Http\Controllers\ArticleController@fill');

Router::get('/articles', 'App\Http\Controllers\ArticleController@index'); // Получить список
Router::post('/articles', 'App\Http\Controllers\ArticleController@store'); // Создание
Router::get('/articles/{article_id}', 'App\Http\Controllers\ArticleController@show'); // Получение сущности по ID
Router::put('/articles/{article_id}', 'App\Http\Controllers\ArticleController@update'); // Изменение
Router::delete('/articles/{article_id}', 'App\Http\Controllers\ArticleController@destroy'); // Удаление
