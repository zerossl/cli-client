<?php

namespace ZeroSSL\CliClient\Http;

use ZeroSSL\CliClient\Enum\HTTPMethod;

class ApiEndpoint
{
    public string $url;
    public HTTPMethod $HTTPMethod;
    public array $postParams;

    /**
     * @param string $url
     * @param array $postParams
     * @param HTTPMethod $HTTPMethod
     */
    public function __construct(string $url, array $postParams, HTTPMethod $HTTPMethod)
    {
        $this->url = $url;
        $this->postParams = $postParams;
        $this->HTTPMethod = $HTTPMethod;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getPostParams(): array
    {
        return $this->postParams;
    }

    /**
     * @return HTTPMethod
     */
    public function getHTTPMethod(): HTTPMethod
    {
        return $this->HTTPMethod;
    }
}
