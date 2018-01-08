# Controller Helper Traits

This library includes two helper traits that can be used by controllers that return PSR-7 `ResponseInterface` objects.

## MessageHelper

The `Caridea\Http\MessageHelper` trait makes sending typical HTTP responses a little easier. It has a few methods of note.

### Working with the Message Body

#### `getParsedBodyArray`
The `getParsedBodyArray` method coerces the return value of the `\Psr\Http\Message\ServerRequestInterface::getParsedBody` method into an `array`.

```php
$body = $this->getParsedBodyArray($request);
// $body will always be an array
```
#### `write`

The `write` method simply coerces a value to a `string`, writes it to the response body, and then returns the response.

```php
// do this:
return $this->write($response, $body);

// instead of:
$response->getBody()->write((string) $body);
return $response;
```

### Caching

#### `ifModSince`

The `ifModSince` method checks the `If-Modified-Since` header, maybe sending 304 Not Modified.

```php
$lastMod = strtotime("-1 day");
$response = $this->ifModSince($request, $response, $lastMod);
if ($response->getStatusCode() != 304) {
    // assemble and write the response body
}
return $response;
```

#### `ifNoneMatch`

The `ifNoneMatch` method checks the `If-None-Match` header, maybe sending 304 Not Modified.


```php
$etag = "foobar";
$response = $this->ifNoneMatch($request, $response, $etag);
if ($response->getStatusCode() != 304) {
    // assemble and write the response body
}
return $response;
```

### Other

* `redirect` – Sets the status, sends the `Location` header, and returns the response.
* `paginationFactory` – Returns a new `Caridea\Http\PaginationFactory` (see [chapter three](03-uri.md))

## JsonHelper

The `Caridea\Http\JsonHelper` trait makes sending JSON responses a little easier. It has a few methods of note.

### Typical JSON Payloads

Generally speaking, the results of a REST GET request should be either an object or an array.

#### `sendJson`

The `sendJson` method encodes a value as JSON, sets the appropriate `Content-Type` header, writes the value to the response body, and returns the response.

```php
$payload = [
    'foo' => 'bar',
    'items' => [
        1, 2, 3,
    ],
];
$this->sendJson($response, $payload);
```

#### `sendItems`

The `sendItems` method encodes an `iterable` value as a JSON array, sets the appropriate `Content-Type` header, sets a `Content-Range` header with appropriate pagination details, writes the value to the response body, and returns the response.

The value in the `Content-Range` header is of the format `items x-y/z`, where *x* is the offset, *y* is the index of the last item returned, and *z* is the total number of available items. (This `Content-Range` output is compatible with [Dojo Toolkit](https://github.com/dojo/dojox)'s `dojox.rpc.Rest` class, and [dstore](https://github.com/SitePen/dstore)'s `Rest` adapter.)

In this example, let's say there are 5 total records, but the user has specified they want 3 items with an offset of 2.

```php
$pagination = new \Caridea\Http\Pagination(3, 2);
$total = 5;
$items = ['foo', 'bar', 'baz'];
$output = $this->sendItems($response, $items, $pagination, $total);
```

The `Content-Range` header would have the value of `items 2-4/5`.

### CRUD Operations

There are several methods to be used by REST endpoints that perform operations, such as `POST`, `DELETE`, and `PUT`.

These methods send a JSON output to the client in the format (where `%s` is some past-tense verb):

```json
{
    "success": true,
    "message": "Objects %s successfully",
    "objects": [
        {
            "type": "foo",
            "id": "bar"
        }
    ]
}
```

All of these methods delegate to `sendVerb`, which accepts the placeholder verb, the PSR-7 `ResponseInterface`, the entity type, a list of entity identifiers, and an optional associative array of additional entries to include in the serialized object.

* `sendCreated` – Sets the message to `Objects created successfully` (and sets the HTTP status code to `201 Created`)
* `sendDeleted` – Sets the message to `Objects deleted successfully`
* `sendUpdated` – Sets the message to `Objects updated successfully`
