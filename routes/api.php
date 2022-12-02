<?php

use App\Foundation\Router\Router;

//Router::get('/articles/clear', 'App\Http\Controllers\ArticleController@clear');
//Router::get('/articles/fill', 'App\Http\Controllers\ArticleController@fill');

Router::get('/api/articles', 'App\Http\Controllers\ArticleController@index'); // Получить список
Router::post('/api/articles', 'App\Http\Controllers\ArticleController@store'); // Создание
Router::get('/api/articles/{key}', 'App\Http\Controllers\ArticleController@show'); // Получение сущности по ID
Router::put('/api/articles/{key}', 'App\Http\Controllers\ArticleController@update'); // Изменение
Router::delete('/api/articles/{key}', 'App\Http\Controllers\ArticleController@destroy'); // Удаление
