<?php

namespace App\Foundation\HTTP;

class Response
{
    /** @var string  */
    protected string $body = '';

    /** @var array<string, mixed> */
    protected array $content;

    /** @var array<string, mixed> */
    protected array $headers = [];

    /** @var int  */
    protected int $code = 200;

    public function __construct(
        array $content = [],
        int $code = 200,
        array $headers = []
    )
    {
        $this->setContent($content);
        $this->setCode($code);
        $this->setHeaders($headers);
    }

    public function send(): void
    {
        $this->addHeaderToResponse();

        if ($this->getBody() == '') {
            $this->setBody($this->prepareContent());
        }

        echo $this->getBody();
    }

    protected function prepareContent(): string
    {
        return json_encode($this->content);
    }

    protected function addHeaderToResponse(): void
    {
        if (!array_key_exists('content-type', $this->headers)) {
            $this->setHeader('content-type', 'application/json');
        }

        foreach ($this->headers as $header_name => $header_value) {
            if (is_array($header_value)) {
                $header_value = implode('; ', $header_value);
            }

            header($header_name . ': ' . $header_value);
        }
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param array $content
     */
    public function setContent(array $content): void
    {
        $this->content = $content;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        foreach ($headers as $header_name => $header_value) {
            $this->setHeader($header_name, $header_value);
        }
    }

    public function setHeader($header, $value): void
    {
        if (is_array($value)) {
            $value = implode('; ', $value);
        }

        $this->headers[strtolower($header)] = $value;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }
}