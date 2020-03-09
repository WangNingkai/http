<?php
/**
 * This file is part of the wangningkai/http.
 * (c) wangningkai <i@ningkai.wang>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Http;

class HttpResponse
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * @var \GuzzleHttp\Psr7\Response $response
     */
    public $response;

    public $cookies;

    public $transferStats;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function body()
    {
        return (string)$this->response->getBody();
    }

    public function json()
    {
        return json_decode($this->response->getBody(), true);
    }

    public function header($header)
    {
        return $this->response->getHeaderLine($header);
    }

    public function headers()
    {
        return collect($this->response->getHeaders())->mapWithKeys(function ($v, $k) {
            return [$k => $v[0]];
        })->all();
    }

    public function status()
    {
        return $this->response->getStatusCode();
    }

    public function effectiveUri()
    {
        return $this->transferStats->getEffectiveUri();
    }

    public function isSuccess()
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    public function isOk()
    {
        return $this->isSuccess();
    }

    public function isRedirect()
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    public function isClientError()
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    public function isServerError()
    {
        return $this->status() >= 500;
    }

    public function cookies()
    {
        return $this->cookies;
    }

    public function __toString()
    {
        return $this->body();
    }

    public function __call($method, $args)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $args);
        }

        return $this->response->{$method}(...$args);
    }
}
