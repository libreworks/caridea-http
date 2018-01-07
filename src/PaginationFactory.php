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
 * @copyright 2015-2018 LibreWorks contributors
 * @license   Apache-2.0
 */
namespace Caridea\Http;

/**
 * Produces Pagination objects based on request parameters.
 *
 * @copyright 2015-2018 LibreWorks contributors
 * @license   Apache-2.0
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
    const RANGE = "Range";
    const REGEX_RANGE = '/^items=(\\d+)-(\\d+)$/';
    const REGEX_DOJO_SORT = '/^sort\\(.*\\)$/';
    
    private static $maxAlias = [self::MAX => null, self::LIMIT => null, self::COUNT => null];
    private static $offsetAlias = [self::START => null, self::OFFSET => null];
    private static $pageAlias = [self::PAGE => null, self::START_PAGE => null];
    
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
     * Because of the fact that many order syntaxes use multiple query string
     * parameters with the same name, it is absolutely *vital* that you do not
     * use a `ServerRequestInterface` that has been constructed with the `$_GET`
     * superglobal.
     *
     * The problem here is that PHP will overwrite entries in the `$_GET`
     * superglobal if they share the same name. With PHP, a request to
     * `file.php?foobar=foo&foobar=bar` will result in `$_GET` set to
     * `['foobar' => 'bar']`.
     *
     * Other platforms, like the Java Servlet specification, allow list-like
     * access to these parameters. Make sure the object you pass for `$request`
     * has a `queryParams` property that has been created to account for
     * multiple query parameters with the same name. The `QueryParams` class
     * will produce an array that accounts for this case.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The server request. Please read important docs above.
     * @param string $sortParameter The name of the sort parameter
     * @param array<string,bool> $defaultSort The default sort if request lacks it
     * @return \Caridea\Http\Pagination The pagination details
     */
    public function create(\Psr\Http\Message\ServerRequestInterface $request, string $sortParameter = self::SORT, array $defaultSort = []): Pagination
    {
        $offset = 0;
        $max = PHP_INT_MAX;
        
        $params = $request->getQueryParams();
        $range = $request->getHeaderLine(self::RANGE);
        
        if ($range !== null && preg_match(self::REGEX_RANGE, $range, $rm)) {
            // dojo.store.JsonRest style
            $offset = (int)$rm[1];
            $max = (int)$rm[2] - $offset + 1;
        } else {
            $max = $this->parse(self::$maxAlias, $params, PHP_INT_MAX);
            // Grails, ExtJS, dojox.data.QueryReadStore, all zero-based
            $offVal = $this->parse(self::$offsetAlias, $params, 0);
            if ($offVal > 0) {
                $offset = $offVal;
            } elseif (isset($params[self::START_INDEX])) {
            // OpenSearch style, 1-based
                $startIdx = isset($params[self::START_INDEX]) ? (int)$params[self::START_INDEX] : 0;
                if ($startIdx > 0) {
                    $offset = $startIdx - 1;
                }
            } elseif (isset($params[self::START_PAGE]) || isset($params[self::PAGE])) {
            // OpenSearch or Spring Data style, 1-based
                $startPage = $this->parse(self::$pageAlias, $params, 0);
                if ($startPage > 0) {
                    $offset = ($max * ($startPage - 1));
                }
            }
        }
        
        return new Pagination($max, $offset, $this->getOrder($request, $sortParameter, $defaultSort));
    }
    
    /**
     * Parses the order array.
     *
     * Because of the fact that many order syntaxes use multiple query string
     * parameters with the same name, it is absolutely *vital* that you do not
     * use a `ServerRequestInterface` that has been constructed with the `$_GET`
     * superglobal.
     *
     * The problem here is that PHP will overwrite entries in the `$_GET`
     * superglobal if they share the same name. With PHP, a request to
     * `file.php?foobar=foo&foobar=bar` will result in `$_GET` set to
     * `['foobar' => 'bar']`.
     *
     * Other platforms, like the Java Servlet specification, allow list-like
     * access to these parameters. Make sure the object you pass for `$request`
     * has a `queryParams` property that has been created to account for
     * multiple query parameters with the same name. The `QueryParams` class
     * will produce an array that accounts for this case.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @param string $sortParameter The sort parameter
     * @param array<string,bool> $default The default sort order
     * @return array<string,bool> String keys to boolean values
     */
    protected function getOrder(\Psr\Http\Message\ServerRequestInterface $request, string $sortParameter, array $default = []): array
    {
        $order = [];
        $params = $request->getQueryParams();
        if (isset($params[$sortParameter])) {
            if (isset($params[self::ORDER])) {
            // stupid Grails ordering
                $order[$params[$sortParameter]] = strcasecmp(self::DESC, $params[self::ORDER]) !== 0;
            } else {
                $param = $params[$sortParameter];
                foreach ((is_array($param) ? $param : [$param]) as $s) {
                    $this->parseSort($s, $order);
                }
            }
        } else {
            foreach (preg_grep(self::REGEX_DOJO_SORT, array_keys($params)) as $s) {
                // sort(+foo,-bar)
                $this->parseSort(substr($s, 5, -1), $order);
            }
        }
        return empty($order) ? $default : $order;
    }
    
    /**
     * Attempts to parse a single sort value.
     *
     * @param string $sort The sort value
     * @param array<string,bool> $sorts String keys to boolean values
     */
    protected function parseSort(string $sort, array &$sorts)
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
        } elseif (substr($sort, -5) == ",desc") {
        // foo,desc
            $sorts[substr($sort, 0, strlen($sort) - 5)] = false;
        } elseif (substr($sort, -10) == ":ascending") {
        // foo:ascending
            $sorts[substr($sort, 0, strlen($sort) - 10)] = true;
        } elseif (substr($sort, -11) == ":descending") {
        // foo:descending
            $sorts[substr($sort, 0, strlen($sort) - 11)] = false;
        } else {
            foreach (explode(',', $sort) as $s) {
                if (substr($s, 0, 1) === '-') {
                // -foo
                    $sorts[substr($s, 1)] = false;
                } elseif (substr($s, 0, 1) === '+') {
                // +foo
                    $sorts[substr($s, 1)] = true;
                } else {
                // foo
                    $sorts[$s] = true;
                }
            }
        }
    }

    /**
     * Gets the first numeric value from `$params`, otherwise `$defaultValue`.
     *
     * @param array $names
     * @param array $params
     * @param int $defaultValue
     * @return int
     */
    protected function parse(array &$names, array &$params, int $defaultValue) : int
    {
        $value = array_reduce(array_intersect_key($params, $names), function ($carry, $item) {
            return $carry !== null ? $carry : (is_numeric($item) ? (int)$item : null);
        });
        return $value === null ? $defaultValue : $value;
    }
}
