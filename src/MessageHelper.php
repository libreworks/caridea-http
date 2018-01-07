<?php
declare(strict_types=1);
/**
 * Caridea
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * @copyright 2015-2017 Appertly
 * @copyright 2017-2018 LibreWorks contributors
 * @license   Apache-2.0
 */
namespace Caridea\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Caridea\Http\PaginationFactory;

/**
 * Controller trait with some handy methods.
 */
trait MessageHelper
{
    /**
     * Gets an associative array of the request body content.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  The request
     * @return array<string,mixed>  The associative array of request body content
     */
    protected function getParsedBodyArray(Request $request): array
    {
        $body = $request->getParsedBody();
        return is_array($body) ? $body : [];
    }

    /**
     * Cleanly writes the body to the response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response  The HTTP response
     * @param mixed $body  The body to write
     * @return \Psr\Http\Message\ResponseInterface  The same or new response
     */
    protected function write(Response $response, $body): Response
    {
        $response->getBody()->write((string) $body);
        return $response;
    }

    /**
     * Checks the `If-Modified-Since` header, maybe sending 304 Not Modified.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  The HTTP request
     * @param \Psr\Http\Message\ResponseInterface $response  The HTTP response
     * @param int $timestamp  The timestamp for comparison
     * @return \Psr\Http\Message\ResponseInterface  The same or new response
     */
    protected function ifModSince(Request $request, Response $response, int $timestamp): Response
    {
        $ifModSince = $request->getHeaderLine('If-Modified-Since');
        if ($ifModSince && $timestamp <= strtotime($ifModSince)) {
            return $response->withStatus(304, "Not Modified");
        }
        return $response;
    }

    /**
     * Checks the `If-None-Match` header, maybe sending 304 Not Modified.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  The HTTP request
     * @param \Psr\Http\Message\ResponseInterface $response  The HTTP response
     * @param string $etag  The ETag for comparison
     * @return \Psr\Http\Message\ResponseInterface  The same or new response
     */
    protected function ifNoneMatch(Request $request, Response $response, string $etag): Response
    {
        $ifNoneMatch = $request->getHeaderLine('If-None-Match');
        if ($ifNoneMatch && $etag === $ifNoneMatch) {
            return $response->withStatus(304, "Not Modified");
        }
        return $response;
    }

    /**
     * Redirects the user to another URL.
     *
     * @param \Psr\Http\Message\ResponseInterface $response  The HTTP response
     * @return \Psr\Http\Message\ResponseInterface  The new response
     */
    protected function redirect(Response $response, int $code, string $url): Response
    {
        return $response->withStatus($code)->withHeader('Location', $url);
    }

    /**
     * Gets a pagination factory
     *
     * @return \Caridea\Http\PaginationFactory  The pagination factory
     */
    protected function paginationFactory(): PaginationFactory
    {
        return new PaginationFactory();
    }
}
