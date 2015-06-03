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
 * Generated by PHPUnit_SkeletonGenerator on 2015-06-03 at 17:07:34.
 */
class PaginationFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaginationFactory
     */
    protected $object;
    
    protected function setUp()
    {
        $this->object = new PaginationFactory();
    }
    
	public function testDojoStore()
	{
		$request1 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams', 'getHeaderLine']);
        $request1->expects($this->any())
            ->method('getHeaderLine')
            ->with($this->equalTo('Range'))
            ->willReturn('items=1-25');
        $request1->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['sort(+foo,-bar)' => '']);
		$p1 = $this->object->create($request1);
		
		$this->assertEquals(25, $p1->getMax());
		$this->assertEquals(1, $p1->getOffset());
		$this->assertEquals(["foo" => true, "bar" => false], $p1->getOrder());
		
		$request2 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams']);
        $request2->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['sort(+foo)' => '']);
		$p2 = $this->object->create($request2);
		
		$this->assertEquals(PHP_INT_MAX, $p2->getMax());
		$this->assertEquals(0, $p2->getOffset());
		$this->assertEquals(["foo" => true], $p2->getOrder());
		
        $uri = $this->getMockForAbstractClass(\Psr\Http\Message\UriInterface::class, ['getQuery']);
        $uri->expects($this->any())
            ->method('getQuery')
            ->willReturn('sortBy=' . urlencode('+foo,-bar,baz'));
		$request3 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams', 'getHeaderLine', 'getUri']);
        $request3->expects($this->any())
            ->method('getHeaderLine')
            ->with($this->equalTo('Range'))
            ->willReturn('items=0-');
        $request3->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['sortBy' => '+foo,-bar,baz']);
        $request3->expects($this->any())
            ->method('getUri')
            ->willReturn($uri);
		$p3 = $this->object->create($request3, "sortBy");
		
		$this->assertEquals(PHP_INT_MAX, $p3->getMax());
		$this->assertEquals(0, $p3->getOffset());
		$this->assertEquals(["foo" => true, "bar" => false, "baz" => true], $p3->getOrder());
	}
	
	public function testDojoData()
	{
        $uri = $this->getMockForAbstractClass(\Psr\Http\Message\UriInterface::class, ['getQuery']);
        $uri->expects($this->any())
            ->method('getQuery')
            ->willReturn('count=25&start=1&sort=' . urlencode('+foo,-bar'));
		$request1 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams', 'getUri']);
        $request1->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['count' => '25', 'start' => '1', 'sort' => '+foo,-bar']);
        $request1->expects($this->any())
            ->method('getUri')
            ->willReturn($uri);
		$p1 = $this->object->create($request1);
		
		$this->assertEquals(25, $p1->getMax());
		$this->assertEquals(1, $p1->getOffset());
		$this->assertEquals(["foo" => true, "bar" => false], $p1->getOrder());
	}
	
	public function testExtJs(){
        $uri = $this->getMockForAbstractClass(\Psr\Http\Message\UriInterface::class, ['getQuery', 'getUri']);
        $uri->expects($this->any())
            ->method('getQuery')
            ->willReturn('start=1&limit=25&sort=' . urlencode("[{\"property\":\"foo\",\"direction\":\"asc\"},{\"property\":\"bar\",\"direction\":\"desc\"}]"));
        $request1 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams']);
        $request1->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['start' => '1', 'limit' => '25', 'sort' => "[{\"property\":\"foo\",\"direction\":\"asc\"},{\"property\":\"bar\",\"direction\":\"desc\"}]"]);
        $request1->expects($this->any())
            ->method('getUri')
            ->willReturn($uri);
		$p1 = $this->object->create($request1);
		
		$this->assertEquals(25, $p1->getMax());
		$this->assertEquals(1, $p1->getOffset());
		$this->assertEquals(["foo" => true, "bar" => false], $p1->getOrder());
	}
	
	public function testOpenSearchServer()
	{
        $uri = $this->getMockForAbstractClass(\Psr\Http\Message\UriInterface::class, ['getQuery']);
        $uri->expects($this->any())
            ->method('getQuery')
            ->willReturn('startIndex=2&count=25&sort=foo,-bar');
        $request1 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams', 'getUri']);
        $request1->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['startIndex' => '2', 'limit' => '25', 'sort' => '-bar']);
        $request1->expects($this->any())
            ->method('getUri')
            ->willReturn($uri);
		$p1 = $this->object->create($request1);
		
		$this->assertEquals(25, $p1->getMax());
		$this->assertEquals(1, $p1->getOffset());
		$this->assertEquals(["foo" => true, "bar" => false], $p1->getOrder());
		
        $uri2 = $this->getMockForAbstractClass(\Psr\Http\Message\UriInterface::class, ['getQuery']);
        $uri2->expects($this->any())
            ->method('getQuery')
            ->willReturn('startIndex=2&count=25&sort=foo,-bar');
        $request2 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams', 'getUri']);
        $request2->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['startPage' => '2', 'count' => '25', 'sort' => '-bar']);
        $request2->expects($this->any())
            ->method('getUri')
            ->willReturn($uri2);
		$p2 = $this->object->create($request2);
		
		$this->assertEquals(25, $p2->getMax());
		$this->assertEquals(25, $p2->getOffset());
		$this->assertEquals(["foo" => true, "bar" => false], $p2->getOrder());
	}
	
	public function testOpenSearchExtension()
	{
        $uri = $this->getMockForAbstractClass(\Psr\Http\Message\UriInterface::class, ['getQuery']);
        $uri->expects($this->any())
            ->method('getQuery')
            ->willReturn('startIndex=2&count=25&sort=foo:ascending&sort=bar:descending');
        $request1 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams', 'getUri']);
        $request1->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['startIndex' => '2', 'count' => '25', 'sort' => 'bar:descending']);
        $request1->expects($this->any())
            ->method('getUri')
            ->willReturn($uri);
		$p1 = $this->object->create($request1);        
		
		$this->assertEquals(25, $p1->getMax());
		$this->assertEquals(1, $p1->getOffset());
		$this->assertEquals(["foo" => true, "bar" => false], $p1->getOrder());
		
        $uri2 = $this->getMockForAbstractClass(\Psr\Http\Message\UriInterface::class, ['getQuery']);
        $uri2->expects($this->any())
            ->method('getQuery')
            ->willReturn('startPage=2&count=25&sort=foo:ascending&sort=bar:descending');
        $request2 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams', 'getUri']);
        $request2->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['startPage' => '2', 'count' => '25', 'sort' => 'bar:descending']);
        $request2->expects($this->any())
            ->method('getUri')
            ->willReturn($uri2);
		$p2 = $this->object->create($request2);
		
		$this->assertEquals(25, $p2->getMax());
		$this->assertEquals(25, $p2->getOffset());
		$this->assertEquals(["foo" => true, "bar" => false], $p2->getOrder());
	}
	
	public function testSpringData()
	{
        $uri = $this->getMockForAbstractClass(\Psr\Http\Message\UriInterface::class, ['getQuery']);
        $uri->expects($this->any())
            ->method('getQuery')
            ->willReturn('page=2&limit=25&sort=foo,asc&sort=bar,desc');
        $request2 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams', 'getUri']);
        $request2->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['page' => 2, 'limit' => 25, 'sort' => 'bar,desc']);
        $request2->expects($this->any())
            ->method('getUri')
            ->willReturn($uri);
		$p2 = $this->object->create($request2);
		
		$this->assertEquals(25, $p2->getMax());
		$this->assertEquals(25, $p2->getOffset());
		$this->assertEquals(["foo" => true, "bar" => false], $p2->getOrder());
	}
	
	public function testGrails()
	{
        $request1 = $this->getMockForAbstractClass(\Psr\Http\Message\ServerRequestInterface::class, ['getQueryParams']);
        $request1->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['max' => '25', 'offset' => '1', 'sort' => 'foo', 'order' => 'asc']);
		$p1 = $this->object->create($request1);
		
		$this->assertEquals(25, $p1->getMax());
		$this->assertEquals(1, $p1->getOffset());
		$this->assertEquals(["foo" => true], $p1->getOrder());
	}
}
