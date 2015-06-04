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
     * @var array
     */
    private $params = [];
    
    /**
     * Creates a new QueryParams object.
     * 
     * @param \Psr\Http\Message\UriInterface $uri The URI
     */
    public function __construct(\Psr\Http\Message\UriInterface $uri)
    {
        foreach (explode('&', $uri->getQuery()) as $pair) {
            list($name, $value) = array_map('urldecode', explode('=', $pair, 2));
            if (substr($name, -2, 2) == '[]') {
                $name = substr($name, 0, strlen($name) - 2);
            }
            $this->params[$name][] = $value;
        }
    }
    
    /**
     * Gets the values for a query parameter.
     * 
     * @param string $name The query parameter name
     * @return array The values for the query parameter (empty array if the parameter isn't present)
     */
    public function get($name)
    {
       return isset($this->params[$name]) ? $this->params[$name] : []; 
    }
    
    /**
     * Gets the query parameters.
     * 
     * @return array Associative array of query parameters. Each entry is an indexed array.
     */
    public function getAll()
    {
        return $this->params;
    }
}
