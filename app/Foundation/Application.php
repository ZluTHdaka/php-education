<?php
declare(strict_types=1);

namespace App\Foundation;

use App\Common\Patterns\Singleton;
use App\Foundation\HTTP\Request;
use App\Foundation\Router\Router;
use JetBrains\PhpStorm\NoReturn;

/**
 * @method static Application getInstance
 */
class Application extends Singleton
{
    protected string $root_path;
    protected Router $router;

    public function run()
    {
        $this->init();

        $this->initRouter();

        $response = $this->router->execute(
            $this->captureRequest()
        );

        $response->send();

        $this->terminate();
    }

    #[NoReturn] public function terminate(): void
    {
        exit(0);
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->root_path;
    }

    /**
     * @param  string  $root_path
     */
    public function setRootPath(string $root_path): void
    {
        $this->root_path = $root_path;
    }


    public function captureRequest(): Request
    {
        $request = new Request();

        $request->initRequestFromGlobals();

        return $request;
    }

    public function initRouter(): void
    {
        $this->router->compileRoutes();
    }

    protected function init(): void
    {
        $this->router = Router::getInstance();
    }
}
