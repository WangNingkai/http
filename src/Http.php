<?php
/**
 * This file is part of the wangningkai/http.
 * (c) wangningkai <i@ningkai.wang>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Http;

/**
 * Class Http
 * @package Http
 *
 * @method static PendingHttpRequest withOptions($options)
 * @method static PendingHttpRequest withoutRedirecting()
 * @method static PendingHttpRequest withoutVerifying()
 * @method static PendingHttpRequest asJson()
 * @method static PendingHttpRequest asFormParams()
 * @method static PendingHttpRequest asMultipart()
 * @method static PendingHttpRequest bodyFormat($format)
 * @method static PendingHttpRequest contentType($contentType)
 * @method static PendingHttpRequest accept($header)
 * @method static PendingHttpRequest withHeaders($headers)
 * @method static PendingHttpRequest withBasicAuth($username, $password)
 * @method static PendingHttpRequest withDigestAuth($username, $password)
 * @method static PendingHttpRequest withCookies($cookies)
 * @method static PendingHttpRequest timeout($seconds)
 * @method static PendingHttpRequest beforeSending($callback)
 * @method static HttpResponse get($url, $queryParams = [])
 * @method static HttpResponse post($url, $params = [])
 * @method static HttpResponse patch($url, $params = [])
 * @method static HttpResponse put($url, $params = [])
 * @method static HttpResponse delete($url, $params = [])
 * @method static HttpResponse send($method, $url, $options)
 */
class Http
{
    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return PendingHttpRequest::instance()->{$method}(...$args);
    }
}
