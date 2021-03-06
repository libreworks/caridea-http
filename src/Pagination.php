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
 * Container for pagination information (i.e. items per page, item offset, field sort order)
 *
 * @copyright 2015-2018 LibreWorks contributors
 * @license   Apache-2.0
 */
class Pagination
{
    /**
     * @var int The max number of records to return
     */
    protected $max;
    /**
     * @var int The offset in the records
     */
    protected $offset;
    /**
     * @var array<string,bool> Associative array of field names to boolean values
     */
    protected $order = [];
    
    /**
     * Creates a new Pagination object.
     *
     * ```php
     * // equivalent to ORDER BY foo ASC, bar DESC LIMIT 20 OFFSET 10
     * $pagination = new \Caridea\Http\Pagination(20, 10, ['foo' => true, 'bar' => false]);
     * ```
     *
     * @param int $max The max number of records to return. If zero or any negative number is provided, this defaults to `PHP_INT_MAX`
     * @param int $offset The offset in the records. If a negative number is provided, this defaults to `0`
     * @param array<string,bool> $order Associative array of field names to boolean values (`true` = ascending, `false` = descending). Any non-boolean value (`1` included) will evaluate as `false`.
     */
    public function __construct(int $max, int $offset, $order = [])
    {
        $this->max = $this->normalize($max, PHP_INT_MAX);
        $this->offset = $this->normalize($offset, 0);
        foreach ($order as $k => $v) {
            if (strlen(trim($k)) !== 0) {
                $this->order[$k] = $v === true;
            }
        }
    }
    
    private function normalize(int $value, int $default): int
    {
        return $value < 1 ? $default : $value;
    }
    
    /**
     * Gets the max number of records to return.
     *
     * @return int The max number of records to return
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * Gets the offset in the records.
     *
     * @return int The offset in the records
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Gets the field ordering.
     *
     * Array keys are field names, values are `true` for ascending, `false` for descending.
     *
     * @return array<string,bool> The field order
     */
    public function getOrder(): array
    {
        return $this->order;
    }
}
