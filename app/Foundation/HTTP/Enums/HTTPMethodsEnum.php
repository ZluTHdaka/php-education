<?php

namespace App\Foundation\HTTP\Enums;

enum HTTPMethodsEnum: string
{
    case Get = 'GET';
    case Post = 'POST';
    case Put = 'PUT';
    case Delete = 'DELETE';
}