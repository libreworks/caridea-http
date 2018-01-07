# caridea-http
Caridea is a miniscule PHP application library. This shrimpy fellow is what you'd use when you just want some helping hands and not a full-blown framework.

![](http://libreworks.com/caridea-100.png)

This is its HTTP component. It includes small utilities for working with [PSR-7](http://www.php-fig.org/psr/psr-7/) HTTP requests and responses, including:

* An implementation of RFC 7807, "[Problem Details for HTTP APIs](https://tools.ietf.org/html/rfc7807)".
* A utility to parse common pagination parameters from the request
* A utility to correctly parse query strings with multiple parameters having the same name
* A utility to determine a client's preferred accepted MIME type

[![Packagist](https://img.shields.io/packagist/v/caridea/http.svg)](https://packagist.org/packages/caridea/http)
[![Build Status](https://travis-ci.org/libreworks/caridea-http.svg)](https://travis-ci.org/libreworks/caridea-http)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/libreworks/caridea-http/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/libreworks/caridea-http/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/libreworks/caridea-http/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/libreworks/caridea-http/?branch=master)
[![Documentation Status](http://readthedocs.org/projects/caridea-http/badge/?version=latest)](http://caridea-http.readthedocs.io/en/latest/?badge=latest)

## Installation

You can install this library using Composer:

```console
$ composer require caridea/http
```

* The master branch (version 3.x) of this project requires PHP 7.1 and depends on `psr/http-message`.
* Version 2.x of this project requires PHP 7.0 and depends on `psr/http-message`.
* Version 1.x of this project requires PHP 5.5 and depends on `psr/http-message`.

## Compliance

Releases of this library will conform to [Semantic Versioning](http://semver.org).

Our code is intended to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), and [PSR-4](http://www.php-fig.org/psr/psr-4/). If you find any issues related to standards compliance, please send a pull request!

## Documentation

* Head over to [Read the Docs](http://caridea-http.readthedocs.io/en/latest/)

## Examples

Just a few quick examples.

### Problem Details

We included an implementation of RFC 7807 that you can serialize to JSON or
append to a PSR-7 HTTP Response.

```php
use Caridea\Http\ProblemDetails;
use Zend\Diactoros\Uri;

$problem = new ProblemDetails(
    new Uri('http://example.com/problem/oops'),  // type
    'A weird thing happened',                    // title
    500,                                         // status
    'It looks like the server has goofed again', // detail
    new Uri('http://example.com/problems/1f9a'), // instance
    [                                            // extensions
        'server' => 'workerbee01.example.com',
        'auth' => 'foobar'
    ]
);
echo json_encode($problem);

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

### Accept Types

```php
// say the HTTP_ACCEPT field is text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
$types = new \Caridea\Http\AcceptTypes($_SERVER);
$types->preferred(['application/xml', 'application/json']); // returns application/xml
```

### Helper Traits

Two traits are now available, `JsonHelper` and `MessageHelper`. These can be used by controller classes or dispatcher middleware.

## Third-Party

The traits `JsonHelper` and `MessageHelper` (as well as their unit tests) were ported to PHP from the Labrys library under a compatible Apache 2.0 license.
