<?php
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
 * @copyright 2015 LibreWorks contributors
 * @license   http://opensource.org/licenses/Apache-2.0 Apache 2.0 License
 */
namespace Caridea\Http;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-06-04 at 16:33:33.
 */
class QueryParamsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Caridea\Http\QueryParams::__construct
     * @covers Caridea\Http\QueryParams::getAll
     */
    public function testGetAll()
    {
        $uri = $this->getMockForAbstractClass(\Psr\Http\Message\UriInterface::class, ['getQuery']);
        $uri->expects($this->any())
            ->method('getQuery')
            ->willReturn('test[]=1&test[]=2&foobar=foo&foobar=bar&abc=123&def=%26hey%26');
        $object = new QueryParams($uri);
        $this->assertEquals(['test' => ['1', '2'], 'foobar' => ['foo', 'bar'], 'abc' => ['123'], 'def' => ['&hey&']], $object->getAll());
    }
    
    /**
     * @covers Caridea\Http\QueryParams::__construct
     * @covers Caridea\Http\QueryParams::get
     */
    public function testGet()
    {
        $uri = $this->getMockForAbstractClass(\Psr\Http\Message\UriInterface::class, ['getQuery']);
        $uri->expects($this->any())
            ->method('getQuery')
            ->willReturn('foobar=foo&foobar=bar&abc=123&def=%26hey%26');
        $object = new QueryParams($uri);
        $this->assertEquals(['foo', 'bar'], $object->get('foobar'));
        $this->assertEquals(['123'], $object->get('abc'));
        $this->assertEquals([], $object->get('not'));
    }
}
