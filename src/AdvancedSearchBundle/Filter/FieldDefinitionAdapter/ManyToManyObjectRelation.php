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

use DivanteLtd\AdvancedSearchBundle\Filter\FieldSelectionInformation;
use DivanteLtd\AdvancedSearchBundle\Filter\FilterEntry;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use Pimcore\Model\DataObject\AbstractObject;

class ManyToManyObjectRelation extends ManyToOneRelation implements IFieldDefinitionAdapter
{
    /**
     * field type for search frontend
     *
     * @var string
     */
    protected $fieldType = "manyToManyObjectRelation";


    /**
     * returns selectable fields with their type information for search frontend
     *
     * @return FieldSelectionInformation[]
     */
    public function getFieldSelectionInformation()
    {
        $allowedTypes = [];
        $allowedClasses = [];
        $allowedTypes[] = ["object", "object_ids"];
        $allowedTypes[] = ["object_filter", "object_filter"];

        foreach($this->fieldDefinition->getClasses() as $class) {
            $allowedClasses[] = $class['classes'];
        }

        $fieldOperators = array_map(
            function ($operator) {
                $operator['fieldLabel'] = $this->translator->trans($operator['fieldLabel'], [], 'admin');

                return $operator;
            },
            $this->getFieldOperators()
        );

        return [new FieldSelectionInformation(
            $this->fieldDefinition->getName(),
            $this->fieldDefinition->getTitle(),
            $this->fieldType,
            [
                'operators' => $fieldOperators,
                'allowedTypes' => $allowedTypes,
                'allowedClasses' => $allowedClasses
            ]
        )];
    }

    /**
     * @inheritDoc
     */
    protected function doGetIndexDataValue($object, $ignoreInheritance = false) {
        $value = parent::doGetIndexDataValue($object, $ignoreInheritance);

        //rewrite all types to 'object' since 'variants' are not supported yet.
        $filteredValues = array_map(function($item) {
            return [
                'id' => $item['id'],
                'type' => 'object'
            ];
        }, $value);

        return $filteredValues;
    }
}
