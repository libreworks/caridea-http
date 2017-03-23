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
 * @copyright 2017 LibreWorks contributors
 * @license   Apache-2.0
 */
namespace Caridea\Http;

class MessageHelperTest extends \PHPUnit_Framework_TestCase
{
    use MessageHelper;

    public function testGetParsedBodyArray1()
    {
        $items = ['foo' => 'bar'];
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withParsedBody($items);
        $this->assertEquals($items, $this->getParsedBodyArray($request));
    }

    public function testGetParsedBodyArray2()
    {
        $request = new \Zend\Diactoros\ServerRequest();
        $this->assertEquals([], $this->getParsedBodyArray($request));
    }

    public function testWrite()
    {
        $body = "foobar";
        $response = new \Zend\Diactoros\Response();
        $returned = $this->write($response, $body);
        $this->assertSame($response, $returned);
    }

    public function testIfModSinceYes()
    {
        $now = time();
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withHeader('If-Modified-Since', gmdate('D, d M Y H:i:s ', $now) . 'GMT');
        $response = new \Zend\Diactoros\Response();
        $returned = $this->ifModSince($request, $response, $now);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $returned);
        $this->assertEquals(304, $returned->getStatusCode());
        $this->assertEquals('Not Modified', $returned->getReasonPhrase());
    }

    public function testIfModSinceNo()
    {
        $now = time();
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withHeader('If-Modified-Since', gmdate('D, d M Y H:i:s ', $now - 5) . 'GMT');
        $response = new \Zend\Diactoros\Response();
        $returned = $this->ifModSince($request, $response, $now);
        $this->assertSame($response, $returned);
        $this->assertEquals(200, $returned->getStatusCode());
        $this->assertEquals('OK', $returned->getReasonPhrase());
    }

    public function testIfModSinceNo2()
    {
        $now = time();
        $request = new \Zend\Diactoros\ServerRequest();
        $response = new \Zend\Diactoros\Response();
        $returned = $this->ifModSince($request, $response, $now);
        $this->assertSame($response, $returned);
        $this->assertEquals(200, $returned->getStatusCode());
        $this->assertEquals('OK', $returned->getReasonPhrase());
    }

    public function testIfNoneMatchYes()
    {
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withHeader('If-None-Match', 'foobar');
        $response = new \Zend\Diactoros\Response();
        $returned = $this->ifNoneMatch($request, $response, 'foobar');
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $returned);
        $this->assertEquals(304, $returned->getStatusCode());
        $this->assertEquals('Not Modified', $returned->getReasonPhrase());
    }

    public function testIfNoneMatchNo()
    {
        $request = new \Zend\Diactoros\ServerRequest();
        $request = $request->withHeader('If-None-Match', 'barfoo');
        $response = new \Zend\Diactoros\Response();
        $returned = $this->ifNoneMatch($request, $response, 'foobar');
        $this->assertSame($response, $returned);
        $this->assertEquals(200, $returned->getStatusCode());
        $this->assertEquals('OK', $returned->getReasonPhrase());
    }

    public function testIfNoneMatchNo2()
    {
        $request = new \Zend\Diactoros\ServerRequest();
        $response = new \Zend\Diactoros\Response();
        $returned = $this->ifNoneMatch($request, $response, 'foobar');
        $this->assertSame($response, $returned);
        $this->assertEquals(200, $returned->getStatusCode());
        $this->assertEquals('OK', $returned->getReasonPhrase());
    }

    public function testRedirect()
    {
        $response = new \Zend\Diactoros\Response();
        $returned = $this->redirect($response, 303, '/other');
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $returned);
        $this->assertEquals(303, $returned->getStatusCode());
        $this->assertEquals('/other', $returned->getHeaderLine('Location'));
    }

    public function testPaginationFactory()
    {
        $this->assertInstanceOf(PaginationFactory::class, $this->paginationFactory());
    }
}
