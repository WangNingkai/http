<?php
/**
 * This file is part of the wangningkai/http.
 * (c) wangningkai <i@ningkai.wang>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Http;

class HttpRequest
{
    /**
     * @var \GuzzleHttp\Psr7\Request $request
     */
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function url()
    {
        return (string)$this->request->getUri();
    }

    public function method()
    {
        return $this->request->getMethod();
    }

    public function body()
    {
        return (string)$this->request->getBody();
    }

    public function headers()
    {
        return collect($this->request->getHeaders())->mapWithKeys(static function ($values, $header) {
            return [$header => $values[0]];
        })->all();
    }
}
