<?php

namespace App\Foundation\Router;

use App\Common\Patterns\Singleton;
use App\Foundation\HTTP\Enums\HTTPMethodsEnum;
use App\Foundation\HTTP\Exceptions\NotFoundException;
use App\Foundation\HTTP\Request;
use App\Foundation\HTTP\Response;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionClass;
use ReflectionException;

/**
 * @method static Router getInstance();
 */
class Router extends Singleton
{
    protected array $compiled_routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    public function compileRoutes(): void
    {
        $route_scheme = require path('routes/api.php');
    }

    public function getCompiledRoutes(): array
    {
        return $this->compiled_routes;
    }

    public static function get(string $url, string $action): Route
    {
        return self::getInstance()->setRouteToCompile(HTTPMethodsEnum::Get, $url, $action);
    }

    public static function post(string $url, string $action): Route
    {
        return self::getInstance()->setRouteToCompile(HTTPMethodsEnum::Post, $url, $action);
    }

    public static function put(string $url, string $action): Route
    {
        return self::getInstance()->setRouteToCompile(HTTPMethodsEnum::Put, $url, $action);
    }

    public static function delete(string $url, string $action): Route
    {
        return self::getInstance()->setRouteToCompile(HTTPMethodsEnum::Delete, $url, $action);
    }

    protected function setRouteToCompile(HTTPMethodsEnum $method, string $url, string $action): Route
    {
        $action_exploded = explode('@', $action);
        if (count($action_exploded) == 1) {
            $complete_action = [
                'class' => $action_exploded[0],
                'method' => 'handle',
            ];
        } else {
            $complete_action = [
                'class' => $action_exploded[0],
                'method' => $action_exploded[1],
            ];
        }

        $regex = self::getRegexFromURL($url);

        $route = new Route(
            method: $method,
            path: $url,
            pattern: $regex['pattern'],
            variables: $regex['variables'],
            controller_class: $complete_action['class'],
            controller_method: $complete_action['method'],
        );

        $this->compiled_routes[$method->value][] = &$route;

        return $route;
    }

    #[ArrayShape(['pattern' => "string", 'variables' => "array"])]
    protected static function getRegexFromURL(string $url): array
    {
        $exploded_url = explode('/', $url);
        $result_url = [];
        $url_variables = [];
        foreach ($exploded_url as $url_token) {
            if (str_starts_with($url_token, '{') && str_ends_with($url_token, '}')) {
                $result_url[] = '(.*)';
                $url_variables[] = substr($url_token, 1, -1);
            } elseif ($url_token == '') {
                $result_url[] = '.*';
            } else {
                $result_url[] = $url_token;
            }
        }

        return [
            'pattern' => '/^' . implode('\/', $result_url) . '$/',
            'variables' => $url_variables
        ];
    }


    /**
     * @throws ReflectionException
     * @throws NotFoundException
     */
    public function execute(Request $request): Response
    {
        /** @var Route $route */
        foreach ($this->compiled_routes[$request->getMethod()->value] as $route) {
            if (preg_match($route->pattern, $request->getPath(), $router_results)) {
                $route_variables = [];
                for ($i = 1, $iMax = count($route->variables); $i <= $iMax; $i++) {
                    if (isset($router_results[$i])) {
                        $route_variables[$route->variables[$i - 1]] = $router_results[$i];
                    }
                }

                $request->setRouterVariables($route_variables);

                $class = $route->controller_class;
                $method = $route->controller_method;

                $executable_method_params = [];
                $controller_reflection = new ReflectionClass($class);

                foreach ($controller_reflection->getMethod($method)->getParameters() as $method_param) {
                    // Если не указывать тип данных в сигнатуре функции контроллера, вернётся null
                    if (is_null($method_param->getType())) {
                        if (array_key_exists($method_param->getName(), $route_variables)) {
                            $executable_method_params[$method_param->getName()] = $route_variables[$method_param->getName()];
                        }
                    } else {
                        $method_param_class = $method_param->getType()?->getName();

                        if ($method_param_class === Request::class) {
                            $executable_method_params[$method_param->getName()] = $request;
                        } elseif (array_key_exists($method_param->getName(), $route_variables)) {
                            $executable_method_params[$method_param->getName()] = $route_variables[$method_param->getName()];
                        }
                    }
                }

                return (new $class())->$method(...$executable_method_params);
            }
        }

        throw new NotFoundException();
    }
}
