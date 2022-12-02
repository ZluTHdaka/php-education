<?php
declare(strict_types=1);

namespace App\Foundation;

use App\Common\Patterns\Singleton;
use App\Foundation\Configuration\Config;
use App\Foundation\Database\DatabaseConnection;
use App\Foundation\HTTP\Request;
use App\Foundation\Router\Router;
use JetBrains\PhpStorm\NoReturn;
use PDO;

/**
 * @method static Application getInstance
 */
class Application extends Singleton
{
    protected string $root_path;
    protected Router $router;
    protected Config $config;
    protected DatabaseConnection $database_connection;

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
     * @return PDO
     */
    public function getDatabaseConnection(): PDO
    {
        return $this->database_connection->getConnection();
    }

    /**
     * @param  DatabaseConnection  $database_connection
     */
    public function setDatabaseConnection(DatabaseConnection $database_connection): void
    {
        $this->database_connection = $database_connection;
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
        $this->config = Config::getInstance();
        $this->config->init();

        $this->setDatabaseConnection(new DatabaseConnection(...config('database.connection')));
    }
}
