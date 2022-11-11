<?php

use App\Foundation\Router\Router;

Router::get('/articles', 'App\Http\Controllers\ArticleController@getArticles');
Router::post('/articles', 'App\Http\Controllers\ArticleController@postArticles');
Router::get('/clear', '\App\Http\Controllers\ArticleController@clear');
Router::get('/fill', '\App\Http\Controllers\ArticleController@fill');