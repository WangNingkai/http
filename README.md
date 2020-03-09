# Http

Http is a simple Guzzle wrapper designed to provide a really pleasant development experience for most common use cases.

If you need more functionality, just use [Guzzle](https://github.com/guzzle/guzzle) :)

```php
$response = Http::withHeaders(['Fancy' => 'Pants'])->post($url, [
    'foo' => 'bar',
    'baz' => 'qux',
]);

$response->status();
// int

$response->isOk();
// true / false

$response->json();
// => [
//  'whatever' => 'was returned',
// ];
```

## Installation

`composer require wangningkai/http`