# URI Related Utilities
This library includes a few utilities that deal with the request URI.

## Pagination

The `Caridea\Http\Pagination` class holds immutable details about limit, offset, and sort order, as specified by a client. It includes only a few methods.

* `getMax` – Gets the maximum number of results a user wants per page
* `getOffset` – Gets the starting record offset
* `getOrder` – Gets an `array` of sort orders, where the keys are the field names, and the values are booleans, `true` for ascending, and `false` for descending

The `Caridea\Http\PaginationFactory` class creates `Pagination` instances based on common parameters specified in a PSR-7 `RequestInterface`. It supports a good size sampling of different standardized ways to present this information in the query string.

Range:
- `max` + `offset` (e.g. Grails): `&max=25&offset=0`
- `count` + `start` (e.g. `dojox.data.QueryReadStore`): `&count=25&start=0`
- `Range` header (e.g. `dojo.store.JsonRest`): `Range: items=0-24`
- `count` + `startIndex` (e.g. OpenSearch): `&count=25&startIndex=1`
- `count` + `startPage` (e.g. OpenSearch): `&count=25&startPage=1`
- `limit` + `page` (e.g. Spring Data REST): `&limit=25&page=1`
- `limit` + `start` (e.g. ExtJS): `&limit=25&start=0`

Dojo, Grails, and ExtJS are all zero-based. OpenSearch and Spring Data are one-based.

Order:
- OpenSearchServer: `&sort=foo&sort=-bar`
- OpenSearch extension: `&sort=foo:ascending&sort=bar:descending`
- Grails: `&sort=foo&order=asc`
- Spring Data REST: `&sort=foo,asc&sort=bar,desc`
- Dojo: `&sort(+foo,-bar)`
- Dojo w/field: `&[field]=+foo,-bar`
- ExtJS JSON: `&sort=[{"property":"foo","direction":"asc"},{"property":"bar","direction":"desc"}]`

Because of the fact that many order syntaxes use multiple query string parameters with the same name, it is absolutely *vital* that you do not use a `ServerRequestInterface` that has been constructed with the `$_GET` superglobal.

The problem here is that PHP will overwrite entries in the `$_GET` superglobal if they share the same name. With PHP, a request to `file.php?foobar=foo&foobar=bar` will result in `$_GET` set to `['foobar' => 'bar']`.

Other platforms, like the Java Servlet specification, allow list-like access to these parameters. Make sure the object you pass for `$request` has a `queryParams` property that has been created to account for multiple query parameters with the same name. The `QueryParams` class (see below) will produce an `array` that accounts for this case.

## Query Parameters

The `Caridea\Http\QueryParams` class correctly parses query strings with multiple parameters having the same name.

As noted in the previous section, the problem is that PHP will overwrite entries in the `$_GET` superglobal if they share the same name. With PHP, a request to `file.php?foobar=foo&foobar=bar` will result in `$_GET` set to `['foobar' => 'bar']`.

Other platforms allow list-like access to these parameters. This class accounts for both of the following cases:
- `param=foo&param=bar`
- `param[]=foo&param[]=bar`

```php
$query = \Caridea\Http\QueryParams::get($_SERVER);
// or:
$query = \Caridea\Http\QueryParams::getFromServer();
```
For the URL `file.php?test[]=1&test[]=2&noval&foobar=foo&foobar=bar&abc=123`, the returned array will be:

```php
[
    'test' => ['1', '2'],
    'noval' => '',
    'foobar' => ['foo', 'bar'],
    'abc' => '123'
];
```

If you were using Zend Diactoros, you might construct your `\Zend\Diactoros\ServerRequest` like so:

```php
$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    \Caridea\Http\QueryParams::getFromServer()
);
```
