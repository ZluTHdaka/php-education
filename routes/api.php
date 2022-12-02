<?php

use App\Foundation\Router\Router;

Router::get('/articles/clear', 'App\Foundation\HTTP\Controllers\ArticleController@clear');
Router::get('/articles/fill', 'App\Foundation\HTTP\Controllers\ArticleController@fill');

Router::get('/articles', 'App\Foundation\HTTP\Controllers\ArticleController@index'); // Получить список
Router::post('/articles', 'App\Foundation\HTTP\Controllers\ArticleController@store'); // Создание
Router::get('/articles/{article_id}', 'App\Foundation\HTTP\Controllers\ArticleController@show'); // Получение сущности по ID
Router::put('/articles/{article_id}', 'App\Foundation\HTTP\Controllers\ArticleController@update'); // Изменение
Router::delete('/articles/{article_id}', 'App\Foundation\HTTP\Controllers\ArticleController@destroy'); // Удаление
