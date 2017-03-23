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

/**
 * @covers \Caridea\Http\JsonHelper
 */
class JsonHelperTest extends \PHPUnit_Framework_TestCase
{
    use JsonHelper;

    public function testSendItems1()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(PHP_INT_MAX, 0);
        $items = ['foo', 'bar', 'baz'];
        $output = $this->sendItems($response, $items, $pagination, 3);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 0-2/3', $output->getHeaderLine('Content-Range'));
    }


    public function testSendItems2()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(3, 2);
        $items = ['foo', 'bar', 'baz'];
        $output = $this->sendItems($response, $items, $pagination, 5);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 2-4/5', $output->getHeaderLine('Content-Range'));
    }


    public function testSendItems3()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(PHP_INT_MAX, 0);
        $items = ['foo', 'bar', 'baz'];
        $output = $this->sendItems($response, $items, $pagination, PHP_INT_MAX);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 0-' . (PHP_INT_MAX - 1) . '/' . PHP_INT_MAX, $output->getHeaderLine('Content-Range'));
    }


    public function testSendItems4()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(PHP_INT_MAX, 5);
        $items = ['foo', 'bar', 'baz'];
        $output = $this->sendItems($response, $items, $pagination, PHP_INT_MAX);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 5-' . (PHP_INT_MAX - 1) . '/' . PHP_INT_MAX, $output->getHeaderLine('Content-Range'));
    }


    public function testSendItems5()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(PHP_INT_MAX, 0);
        $items = [];
        $output = $this->sendItems($response, $items, $pagination);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 0-0/0', $output->getHeaderLine('Content-Range'));
    }


    public function testSendItems6()
    {
        $response = new \Zend\Diactoros\Response();
        $pagination = new \Caridea\Http\Pagination(3, 0);
        $items = ['a', 'b', 'c'];
        $output = $this->sendItems($response, $items, $pagination, 9);
        $this->assertEquals(json_encode($items), (string)$output->getBody());
        $this->assertEquals('items 0-2/9', $output->getHeaderLine('Content-Range'));
    }

    public function testCreated()
    {
        $response = new \Zend\Diactoros\Response();
        $returned = $this->sendCreated($response, 'foobar', [1], ['foo' => 'bar']);
        $this->assertEquals('application/json', $returned->getHeaderLine('Content-Type'));
        $this->assertEquals(201, $returned->getStatusCode());
        $this->assertEquals('Created', $returned->getReasonPhrase());
        $out = [
            'foo' => 'bar',
            'success' => true,
            'message' => 'Objects created successfully',
            'objects' => [
                ['type' => 'foobar', 'id' => 1]
            ]
        ];
        $this->assertEquals(json_encode($out), (string) $returned->getBody());
    }

    public function testDeleted()
    {
        $response = new \Zend\Diactoros\Response();
        $returned = $this->sendDeleted($response, 'foobar', [1], ['foo' => 'bar']);
        $this->assertEquals('application/json', $returned->getHeaderLine('Content-Type'));
        $this->assertEquals(200, $returned->getStatusCode());
        $this->assertEquals('OK', $returned->getReasonPhrase());
        $out = [
            'foo' => 'bar',
            'success' => true,
            'message' => 'Objects deleted successfully',
            'objects' => [
                ['type' => 'foobar', 'id' => 1]
            ]
        ];
        $this->assertEquals(json_encode($out), (string) $returned->getBody());
    }

    public function testUpdated()
    {
        $response = new \Zend\Diactoros\Response();
        $returned = $this->sendUpdated($response, 'foobar', [1], ['foo' => 'bar']);
        $this->assertEquals('application/json', $returned->getHeaderLine('Content-Type'));
        $this->assertEquals(200, $returned->getStatusCode());
        $this->assertEquals('OK', $returned->getReasonPhrase());
        $out = [
            'foo' => 'bar',
            'success' => true,
            'message' => 'Objects updated successfully',
            'objects' => [
                ['type' => 'foobar', 'id' => 1]
            ]
        ];
        $this->assertEquals(json_encode($out), (string) $returned->getBody());
    }
}
