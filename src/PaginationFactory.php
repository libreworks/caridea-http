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
 * Produces Pagination objects based on request parameters.
 *
 * @copyright 2015 LibreWorks contributors
 * @license   http://opensource.org/licenses/Apache-2.0 Apache 2.0 License
 */
class PaginationFactory
{
    const DESC = "desc";
	const SORT = "sort";
	const ORDER = "order";
	const PAGE = "page";
	const START_PAGE = "startPage";
	const START_INDEX = "startIndex";
	const START = "start";
	const COUNT = "count";
	const MAX = "max";
	const LIMIT = "limit";
	const OFFSET = "offset";
	const HEADER_RANGE = "Range";

	private static $RANGE = '/^items=(\\d+)-(\\d+)$/';
	private static $DOJO_SORT = '/^sort\\((.*)\\)$/';
	private static $MAX_ALIAS = [self::MAX, self::LIMIT, self::COUNT];
	private static $OFFSET_ALIAS = [self::START, self::OFFSET];
	private static $PAGE_ALIAS = [self::PAGE, self::START_PAGE];
	
    /**
     * Checks the request query parameters and headers for pagination info.
     * 
     * This class supports a good size sampling of different ways to provide
     * pagination info. 
     * 
     * #### Range
	 * - `max` + `offset` (e.g. Grails): `&max=25&offset=0`
	 * - `count` + `start` (e.g. `dojox.data.QueryReadStore`): `&count=25&start=0`
     * - `Range` header (e.g. `dojo.store.JsonRest`): `Range: items=0-24`
     * - `count` + `startIndex` (e.g. OpenSearch): `&count=25&startIndex=1`
     * - `count` + `startPage` (e.g. OpenSearch): `&count=25&startPage=1`
     * - `limit` + `page` (e.g. Spring Data REST): `&limit=25&page=1`
     * - `limit` + `start` (e.g. ExtJS): `&limit=25&start=0`
     * 
     * Dojo, Grails, and ExtJS are all zero-based. OpenSearch and Spring Data
     * are one-based.
     * 
     * #### Order
     * - OpenSearchServer: `&sort=foo&sort=-bar`
     * - OpenSearch extension: `&sort=foo:ascending&sort=bar:descending`
	 * - Grails: `&sort=foo&order=asc`
	 * - Spring Data REST: `&sort=foo,asc&sort=bar,desc`
     * - Dojo: `&sort(+foo,-bar)`
	 * - Dojo w/field: `&[field]=+foo,-bar`
     * - ExtJS JSON: `&sort=[{"property":"foo","direction":"asc"},{"property":"bar","direction":"desc"}]`
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request The server request
     * @param string $sortParameter The name of the sort parameter
     * @return \Caridea\Http\Pagination The pagination details 
     */
	public function create(\Psr\Http\Message\ServerRequestInterface $request, $sortParameter = self::SORT)
	{
		$offset = 0;
		$max = PHP_INT_MAX;
		
		$params = $request->getQueryParams();
		
		$range = $request->getHeaderLine(self::HEADER_RANGE);
        
		if (preg_match(self::$RANGE, $range, $rm)) {
			// dojo.store.JsonRest style
			$offset = (int)$rm[1];
			$max = (int)$rm[2] - $offset + 1;
		} else {
			$maxVal = self::parse(self::$MAX_ALIAS, $params, PHP_INT_MAX);
			if (PHP_INT_MAX != $maxVal) {
				$max = $maxVal;
			}
			// Grails, ExtJS, dojox.data.QueryReadStore, all zero-based
			$offVal = self::parse(self::$OFFSET_ALIAS, $params, 0);
			if ($offVal > 0) {
				$offset = $offVal;
			}
			if (isset($params[self::START_INDEX]) || isset($params[self::START_PAGE]) || isset($params[self::PAGE])) {
			// OpenSearch style, 1-based
				$startIdx = isset($params[self::START_INDEX]) ? (int)$params[self::START_INDEX] : 0;
			// OpenSearch or Spring Data style, 1-based
				$startPage = self::parse(self::$PAGE_ALIAS, $params, 0);
				if ($startIdx > 0) {
					$offset = $startIdx - 1;
				} else if ($startPage > 0) {
					$offset = ($max * ($startPage - 1));
				}
			}				
		}
		
		return new Pagination($max, $offset, $this->getOrder($request, $sortParameter));
	}
    
    private function getOrder(\Psr\Http\Message\ServerRequestInterface $request, $sortParameter)
    {
		$order = [];
        $params = $request->getQueryParams();
		if (isset($params[$sortParameter])) {
			if (isset($params[self::ORDER])) {
			// stupid Grails ordering
				$order[$params[$sortParameter]] = strcasecmp(self::DESC, $params[self::ORDER]) !== 0;
			} else {
                $values = $this->getExplodedParams($request)[$sortParameter];
                foreach ($values as $s) {
                    self::parseSort($s, $order);
                }
			}
		} else {
			foreach ($params as $s => $v) {
				if (preg_match(self::$DOJO_SORT, $s, $sm)) {
					// +foo,-bar
					self::parseSort($sm[1], $order);
				}
			}
		}
        return $order;
    }
    
    private function getExplodedParams(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $query = $request->getUri()->getQuery();
        $explodedParams = [];
        // in case of multiple query string params with the same name
        // account for both param=foo&param=bar 
        //         and also param[]=foo&param[]=bar
        foreach (explode('&', $query) as $pair) {
            list($name, $value) = explode('=', $pair, 2);  
            $name = urldecode($name);
            if (substr($name, -2, 2) == '[]') {
                $name = substr($name, 0, strlen($name) - 2);
            }
            $explodedParams[$name][] = urldecode($value);
        }        
        return $explodedParams;
    }
	
	private function parseSort($sort, &$sorts)
	{
		if (strlen(trim($sort)) === 0) {
			return;
		}
		if (substr($sort, 0, 1) == "[") {
			// it might be the ridiculous JSON ExtJS sort format
            $json = json_decode($sort);
            if (is_array($json)) {
                foreach ($json as $s) {
                    if (is_object($s)) {
                        $sorts[$s->property] = strcasecmp(self::DESC, $s->direction) !== 0;
                    }
                }
                return;
            }
		}
		if (substr($sort, -4) == ",asc") {
		// foo,asc
			$sorts[substr($sort, 0, strlen($sort) - 4)] = true;
		} else if (substr($sort, -5) == ",desc") {
		// foo,desc
			$sorts[substr($sort, 0, strlen($sort) - 5)] = false;
		} else if (substr($sort, -10) == ":ascending") {
		// foo:ascending
			$sorts[substr($sort, 0, strlen($sort) - 10)] = true;
		} else if (substr($sort, -11) == ":descending") {
		// foo:descending
            $sorts[substr($sort, 0, strlen($sort) - 11)] = false;
		} else {
			foreach (explode(',', $sort) as $s) {
				if (substr($s, 0, 1) === '-') {
				// -foo
                    $sorts[substr($s, 1)] = false;
				} else if (substr($s, 0, 1) === '+') {
				// +foo
                    $sorts[substr($s, 1)] = true;
				} else {
				// foo
                    $sorts[$s] = true;
				}
			}
		}
	}

	private static function parse(array &$names, array &$params, $defaultValue)
	{
		foreach ($names as $name) {
			$value = isset($params[$name]) ? $params[$name] : null;
			$parsed = is_numeric($value) ? (int)$value : null;
			if ($parsed !== null) {
				return (int)$value;
			}
		}
		return $defaultValue;
	}
}
