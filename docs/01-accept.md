# HTTP Accept Header Utility

The HTTP `Accept` header allows a client to advertise which MIME types it prefers. For example, a client might send: `text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8`.

For any given set of alternative content types, we want to be able to easily identify which type a client most prefers. Given the above `Accept` header, we can run the following code:

```php
$types = new \Caridea\Http\AcceptTypes($_SERVER);
$preferredType = $types->preferred(['application/xml', 'application/json']);
```

In this case, `$preferredType` would be `application/xml`.

Here are a few more examples:

```php
// will return 'text/html'
$types->preferred(['text/css', 'text/html', 'application/javascript']);

// will return 'application/xml'
$types->preferred(['application/xml', 'text/css', 'application/json']);

// will return 'text/plain'
$types->preferred(['text/plain', 'text/css', 'application/json']);

// will return null
$types->preferred(['application/json', 'application/octet-stream']);

// will return null
$types->preferred([]);
```
