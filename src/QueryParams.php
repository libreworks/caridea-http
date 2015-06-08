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
 * Parses a query string from a URI and gets its values accurately.
 *
 * The problem here is that PHP will overwrite entries in the `$_GET`
 * superglobal if they share the same name. With PHP, a request to
 * `file.php?foobar=foo&foobar=bar` will result in `$_GET` set to
 * `['foobar' => 'bar']`.
 *
 * Other platforms allow list-like access to these parameters. This class will
 * account for both of the following cases:
 * - `param=foo&param=bar`
 * - `param[]=foo&param[]=bar`
 *
 * @copyright 2015 LibreWorks contributors
 * @license   http://opensource.org/licenses/Apache-2.0 Apache 2.0 License
 */
class QueryParams
{
    /**
     * Parses the query string from the `QUERY_STRING` in the provided array.
     * 
     * For the URL `file.php?test[]=1&test[]=2&noval&foobar=foo&foobar=bar&abc=123`,
     * the returned array will be:
     * 
     * ```php
     * [
     *     'test' => ['1', '2'],
     *     'noval' => '',
     *     'foobar' => ['foo', 'bar'],
     *     'abc' => '123'
     * ];
     * ```
     *
     * @param array $server The server values array
     */
    public static function get(array $server)
    {
        $params = [];
        if (isset($server['QUERY_STRING'])) {
            $query = ltrim($server['QUERY_STRING'], '?');
            foreach (explode('&', $query) as $pair) {
                if ($pair) {
                    list($name, $value) = self::normalize(
                        array_map('urldecode', explode('=', $pair, 2))
                    );
                    $params[$name][] = $value;
                }
            }
        }
        return $params ? array_map(function ($v){
            return count($v) === 1 ? $v[0] : $v;
        }, $params) : $params;
    }
    
    protected static function normalize(array $pair)
    {
        $name = $pair[0];
        if (substr($name, -2, 2) == '[]') {
            $name = substr($name, 0, -2);
        }
        return [$name, count($pair) > 1 ? $pair[1] : ''];
    }
    
    /**
     * Parses the query string from the `QUERY_STRING` in the `$_SERVER` superglobal.
     * 
     * For the URL `file.php?test[]=1&test[]=2&noval&foobar=foo&foobar=bar&abc=123`,
     * the returned array will be:
     * 
     * ```php
     * [
     *     'test' => ['1', '2'],
     *     'noval' => '',
     *     'foobar' => ['foo', 'bar'],
     *     'abc' => '123'
     * ];
     * ```
     */
    public static function getFromServer()
    {
        return self::get($_SERVER);
    }
}
