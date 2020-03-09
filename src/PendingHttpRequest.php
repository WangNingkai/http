<?php
/**
 * This file is part of the wangningkai/http.
 * (c) wangningkai <i@ningkai.wang>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Http;

class PendingHttpRequest
{
    private $beforeSendingCallbacks;

    private $cookies;

    private $bodyFormat;

    private $options;

    private $transferStats;

    /**
     * PendingHttpRequest constructor.
     */
    public function __construct()
    {
        $this->beforeSendingCallbacks = collect(function ($request, $options) {
            $this->cookies = $options['cookies'];
        });
        $this->bodyFormat = 'json';
        $this->options = [
            'http_errors' => false,
        ];
    }

    /**
     * @param mixed ...$args
     * @return PendingHttpRequest
     */
    public static function instance(...$args)
    {
        return new self(...$args);
    }

    /**
     * @param $options
     * @return mixed
     */
    public function withOptions($options)
    {
        return $this->tap($this, function ($request) use ($options) {
            return $this->options = array_merge_recursive($this->options, $options);
        });
    }

    /**
     * @return mixed
     */
    public function withoutRedirecting()
    {
        return $this->tap($this, function ($request) {
            return $this->options = array_merge_recursive($this->options, [
                'allow_redirects' => false,
            ]);
        });
    }

    /**
     * @return mixed
     */
    public function withoutVerifying()
    {
        return $this->tap($this, function ($request) {
            return $this->options = array_merge_recursive($this->options, [
                'verify' => false,
            ]);
        });
    }

    /**
     * @return mixed
     */
    public function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    /**
     * @return mixed
     */
    public function asFormParams()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    /**
     * @return mixed
     */
    public function asMultipart()
    {
        return $this->bodyFormat('multipart');
    }

    /**
     * @param $format
     * @return mixed
     */
    public function bodyFormat($format)
    {
        return $this->tap($this, function ($request) use ($format) {
            $this->bodyFormat = $format;
        });
    }

    /**
     * @param $contentType
     * @return mixed
     */
    public function contentType($contentType)
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    /**
     * @param $header
     * @return mixed
     */
    public function accept($header)
    {
        return $this->withHeaders(['Accept' => $header]);
    }

    /**
     * @param $headers
     * @return mixed
     */
    public function withHeaders($headers)
    {
        return $this->tap($this, function ($request) use ($headers) {
            return $this->options = array_merge_recursive($this->options, [
                'headers' => $headers,
            ]);
        });
    }

    /**
     * @param $username
     * @param $password
     * @return mixed
     */
    public function withBasicAuth($username, $password)
    {
        return $this->tap($this, function ($request) use ($username, $password) {
            return $this->options = array_merge_recursive($this->options, [
                'auth' => [$username, $password],
            ]);
        });
    }

    /**
     * @param $username
     * @param $password
     * @return mixed
     */
    public function withDigestAuth($username, $password)
    {
        return $this->tap($this, function ($request) use ($username, $password) {
            return $this->options = array_merge_recursive($this->options, [
                'auth' => [$username, $password, 'digest'],
            ]);
        });
    }

    /**
     * @param $cookies
     * @return mixed
     */
    public function withCookies($cookies)
    {
        return $this->tap($this, function ($request) use ($cookies) {
            return $this->options = array_merge_recursive($this->options, [
                'cookies' => $cookies,
            ]);
        });
    }

    /**
     * @param $seconds
     * @return mixed
     */
    public function timeout($seconds)
    {
        return $this->tap($this, function () use ($seconds) {
            $this->options['timeout'] = $seconds;
        });
    }

    /**
     * @param $callback
     * @return mixed
     */
    public function beforeSending($callback)
    {
        return $this->tap($this, function () use ($callback) {
            $this->beforeSendingCallbacks[] = $callback;
        });
    }

    /**
     * @param $url
     * @param array $queryParams
     * @return mixed
     * @throws ConnectionException
     */
    public function get($url, $queryParams = [])
    {
        return $this->send('GET', $url, [
            'query' => $queryParams,
        ]);
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws ConnectionException
     */
    public function post($url, $params = [])
    {
        return $this->send('POST', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws ConnectionException
     */
    public function patch($url, $params = [])
    {
        return $this->send('PATCH', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws ConnectionException
     */
    public function put($url, $params = [])
    {
        return $this->send('PUT', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     * @throws ConnectionException
     */
    public function delete($url, $params = [])
    {
        return $this->send('DELETE', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    /**
     * @param $method
     * @param $url
     * @param $options
     * @return mixed
     * @throws ConnectionException
     */
    public function send($method, $url, $options)
    {
        try {
            return $this->tap(new HttpResponse($this->buildClient()->request($method, $url, $this->mergeOptions([
                'query' => $this->parseQueryParams($url),
                'on_stats' => function ($transferStats) {
                    $this->transferStats = $transferStats;
                }
            ], $options))), function ($response) {
                $response->cookies = $this->cookies;
                $response->transferStats = $this->transferStats;
            });
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw new ConnectionException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function buildClient()
    {
        return new \GuzzleHttp\Client([
            'handler' => $this->buildHandlerStack(),
            'cookies' => true,
        ]);
    }

    /**
     * @return mixed
     */
    public function buildHandlerStack()
    {
        return $this->tap(\GuzzleHttp\HandlerStack::create(), function ($stack) {
            /* @var \GuzzleHttp\HandlerStack $stack */
            $stack->push($this->buildBeforeSendingHandler());
        });
    }

    /**
     * @return \Closure
     */
    public function buildBeforeSendingHandler()
    {
        return function ($handler) {
            return function ($request, $options) use ($handler) {
                return $handler($this->runBeforeSendingCallbacks($request, $options), $options);
            };
        };
    }

    /**
     * @param $request
     * @param $options
     * @return mixed
     */
    public function runBeforeSendingCallbacks($request, $options)
    {
        return $this->tap($request, function ($request) use ($options) {
            $this->beforeSendingCallbacks->each->__invoke(new HttpRequest($request), $options);
        });
    }

    /**
     * @param mixed ...$options
     * @return array
     */
    public function mergeOptions(...$options)
    {
        return array_merge_recursive($this->options, ...$options);
    }

    /**
     * @param $url
     * @return mixed
     */
    public function parseQueryParams($url)
    {
        return $this->tap([], static function (&$query) use ($url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
        });
    }

    /**
     * @param $value
     * @param $callback
     * @return mixed
     */
    public function tap($value, $callback)
    {
        $callback($value);
        return $value;
    }
}
