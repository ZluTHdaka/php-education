<?php

namespace App\Foundation\HTTP;

use App\Foundation\HTTP\Enums\HTTPMethodsEnum;

class Request
{
    protected HTTPMethodsEnum $method;
    protected string $uri;
    protected string $path;
    protected string $host;
    protected array $query;
    protected array $headers;
    protected array $body;
    protected array $files;
    protected array $router_variables = [];

    public function initRequestFromGlobals(): void
    {
        $this->setHeaders(getallheaders());
        $this->method = HTTPMethodsEnum::from($_SERVER['REQUEST_METHOD']);
        $this->uri = $_SERVER["REQUEST_URI"];
        $this->path = explode("?", $this->uri)[0];
        $this->host = $_SERVER["HTTP_HOST"];
        $this->headers = $this->getHeaders();
        $this->query = $_GET;
        $this->files = $_FILES;


        if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
            switch ($_SERVER['CONTENT_TYPE']) {
                case 'application/json':
                    $tmp_body = file_get_contents('php://input');
                    $this->body = json_decode($tmp_body, true);
                    break;
                default:
                    $this->body = $_POST;
            }
        } else {
            $this->body = $_POST;
        }
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $header): ?array
    {
        foreach ($this->headers as $key=> $value){
            if ($key == $header){
                $meaning = [$key=>$value];
                return $meaning;
            }
        }
        return null;
    }

    public function hasHeader(string $header): bool
    {
        return array_key_exists($header, $this->headers);
    }

    /**
     * @param array $headars
     */
    public function setHeaders(array $headars): void
    {
        $this->headers = $headars;
    }

    /**
     * @return array
     */
    public function getRouterVariables(): array
    {
        return $this->router_variables;
    }

    /**
     * @param  array  $router_variables
     */
    public function setRouterVariables(array $router_variables): void
    {
        $this->router_variables = $router_variables;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return HTTPMethodsEnum
     */
    public function getMethod(): HTTPMethodsEnum
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @param HTTPMethodsEnum $method
     */
    public function setMethod(HTTPMethodsEnum $method): void
    {
        $this->method = $method;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query): void
    {
        $this->query = $query;
    }

    /**
     * @param array $body
     */
    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    /**
     * @param array $files
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    public function has(string $name): bool
    {
        if (array_key_exists($name, $this->query)) {
            return true;
        }

        if (array_key_exists($name, $this->body)) {
            return true;
        }

        return false;
    }

    public function get(string $name): mixed
    {
        if (helper_array_exists($this->query, $name)) {
            return helper_array_get($this->query, $name);
        }

        if (helper_array_exists($this->body, $name)) {
            return helper_array_get($this->body, $name);
        }

        return null;
    }
}
