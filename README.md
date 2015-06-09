# caridea-http
Caridea is a miniscule PHP web application library. This shrimpy fellow is what you'd use when you just want some helping hands and not a full-blown framework.

![](http://libreworks.com/caridea-100.png)

This is its HTTP component. It includes small utilities for working with [PSR-7](http://www.php-fig.org/psr/psr-7/)HTTP requests and responses, including:

* An implementation of "[Problem Details for HTTP APIs](https://tools.ietf.org/html/draft-ietf-appsawg-http-problem-00)".
* A utility to parse common pagination parameters from the request
* A utility to correctly parse query strings with multiple parameters having the same name

## Installation

You can install this library using Composer:

```console
$ composer require caridea/http
```

This project requires PHP 5.5 and depends on `psr/http-message`.

## Compliance

Releases of this library will conform to [Semantic Versioning](http://semver.org).

Our code is intended to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), and [PSR-4](http://www.php-fig.org/psr/psr-4/). If you find any issues related to standards compliance, please send a pull request!

## Examples

Just a few quick examples.

### Problem Details

```php
use Caridea\Http\ProblemDetails;
use Zend\Diactoros\Uri;

$problem = new ProblemDetails(
    new Uri('http://example.com/problem/oops'),  // type
    'A weird thing happened',                    // title
    500,                                         // status
    'It looks like the server has goofed again', // detail
    new Uri('http://example.com/problems/1f9a'), // instance
    [                                            // detail
        'server' => 'workerbee01.example.com',
        'auth' => 'foobar'
    ]
);
echo $problem->getJson();
```

### Pagination Factory

```php
use Zend\Diactoros\ServerRequestFactory;

$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    \Caridea\Http\QueryParams::getFromServer(), // instead of $_GET
);

$factory = new \Caridea\Http\PaginationFactory();

// say the Query was ?count=25&startIndex=1&sort=%2Bfoo&sort-bar
// or maybe          ?count=25&start=0&sort=%2Bfoo,-bar
// or one of many other formats for this type of pagination settingns
$pagination = $factory->create($request, 'sort');
$pagination->getMax();    // 25
$pagination->getOffset(); // 0
$pagination->getOrder();  // ['foo' => true, 'bar' => false]
```