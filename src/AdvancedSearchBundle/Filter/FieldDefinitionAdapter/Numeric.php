<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter;

use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;

/**
 * Class Numeric
 *
 * @package DivanteLtd\AdvancedSearchBundle\Filter\FieldDefinitionAdapter
 */
class Numeric extends DefaultAdapter implements IFieldDefinitionAdapter
{
    use Operators\NumericOperatorsTrait;

    /**
     * field type for search frontend
     *
     * @var string
     */
    protected $fieldType = 'numeric';

    /**
     * @return array
     */
    public function getESMapping()
    {
        if ($this->considerInheritance) {
            return [
                $this->fieldDefinition->getName(),
                [
                    'properties' => [
                        static::ES_MAPPING_PROPERTY_STANDARD => [
                            'type' => 'float',
                        ],
                        static::ES_MAPPING_PROPERTY_NOT_INHERITED => [
                            'type' => 'float',
                        ],
                    ],
                ],
            ];
        } else {
            return [
                $this->fieldDefinition->getName(),
                [
                    'type' => 'float',
                ],
            ];
        }
    }

    /**
     * @param mixed $fieldFilter
     *
     * filter field format as follows:
     *   - simple number like
     *       234.54   --> creates TermQuery
     *   - array with gt, gte, lt, lte like
     *      ["gte" => 40, "lte" => 45] --> creates RangeQuery
     * @param bool $ignoreInheritance
     * @param string $path
     *
     * @return RangeQuery|TermQuery
     */
    public function getQueryPart($fieldFilter, $ignoreInheritance = false, $path = '')
    {
        if (is_array($fieldFilter)) {
            return new RangeQuery(
                $path . $this->fieldDefinition->getName() . $this->buildQueryFieldPostfix($ignoreInheritance),
                $fieldFilter
            );
        } else {
            return new TermQuery(
                $path . $this->fieldDefinition->getName() . $this->buildQueryFieldPostfix($ignoreInheritance),
                $fieldFilter
            );
        }
    }

    /**
     * @param Concrete $object
     * @param bool $ignoreInheritance
     * @return mixed
     */
    protected function doGetIndexDataValue($object, $ignoreInheritance = false)
    {
        $inheritanceBackup = null;
        if ($ignoreInheritance) {
            $inheritanceBackup = AbstractObject::getGetInheritedValues();
            AbstractObject::setGetInheritedValues(false);
        }

        $value = $this->fieldDefinition->getForWebserviceExport($object);

        if ($ignoreInheritance) {
            AbstractObject::setGetInheritedValues($inheritanceBackup);
        }

        return $value;
    }
}
