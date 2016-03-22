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
 * @copyright 2015-2016 LibreWorks contributors
 * @license   http://opensource.org/licenses/Apache-2.0 Apache 2.0 License
 */
namespace Caridea\Http;

/**
 * Provides utilities for working with the HTTP Accept header.
 *
 * @copyright 2015-2016 LibreWorks contributors
 * @license   http://opensource.org/licenses/Apache-2.0 Apache 2.0 License
 */
class AcceptTypes
{
    /**
     * @var \SplPriorityQueue
     */
    private $types;

    /**
     * Creates a new AcceptTypes.
     *
     * @param array $server The server variables. (e.g. `$_SERVER`)
     */
    public function __construct(array $server)
    {
        $this->types = new \SplPriorityQueue();
        if (isset($server['HTTP_ACCEPT'])) {
            foreach (preg_split('#,\s*#', $server['HTTP_ACCEPT']) as $accept) {
                $split = array_pad(preg_split('#;\s*q=#', $accept, 2), 2, 1.0);
                $this->types->insert($split[0], (float)$split[1]);
            }
        }
    }
    
    /**
     * Returns the most preferred MIME type out of the provided list.
     *
     * This method will iterate through the stored MIME types in preferred order
     * and attempt to match them to those provided.
     *
     * Say for example the HTTP Accept header was:
     * `text/html,application/xhtml+xml,application/xml;q=0.9,text/*;q=0.8`.
     *
     * ```php
     * // will return 'text/html'
     * $types->preferred(['text/css', 'text/html', 'application/javascript']);
     * // will return 'application/xml'
     * $types->preferred(['application/xml', 'text/css', 'application/json']);
     * // will return 'text/plain'
     * $types->preferred(['text/plain', 'text/css', 'application/json']);
     * // will return null
     * $types->preferred(['application/json', 'application/octet-stream']);
     * // will return null
     * $types->preferred([]);
     * ```
     *
     * @param  array $types The MIME types to compare
     * @return string The most preferred MIME type or null
     */
    public function preferred(array $types)
    {
        if (empty($types)) {
            return null;
        }
        foreach ($this->types as $type) {
            if (in_array($type, $types, true)) {
                return $type;
            } elseif ('*/*' == $type) {
                return current($types);
            } elseif (strlen($type) > 2 && substr($type, -2, 2) == '/*') {
                $prefix = substr($type, 0, strpos($type, '/') + 1);
                $plen = strlen($prefix);
                foreach ($types as $t) {
                    if (strncmp($prefix, $t, $plen) === 0) {
                        return $t;
                    }
                }
            }
        }
    }
}
