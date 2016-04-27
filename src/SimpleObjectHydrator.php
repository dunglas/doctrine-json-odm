<?php

/*
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Dunglas\DoctrineJsonOdm;

use Doctrine\ORM\Internal\Hydration\HydrationException;
use Doctrine\ORM\Internal\Hydration\SimpleObjectHydrator as BaseSimpleObjectHydrator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;

class SimpleObjectHydrator extends BaseSimpleObjectHydrator
{
    /**
     * Copied from BaseSimpleObjectHydrator.
     *
     * @var ClassMetadata
     */
    private $class;

    /**
     * Copied from BaseSimpleObjectHydrator.
     *
     * {@inheritdoc}
     */
    protected function prepare()
    {
        if (count($this->_rsm->aliasMap) !== 1) {
            throw new \RuntimeException('Cannot use SimpleObjectHydrator with a ResultSetMapping that contains more than one object result.');
        }

        if ($this->_rsm->scalarMappings) {
            throw new \RuntimeException('Cannot use SimpleObjectHydrator with a ResultSetMapping that contains scalar mappings.');
        }

        $this->class = $this->getClassMetadata(reset($this->_rsm->aliasMap));
    }

    /**
     * Copied from BaseSimpleObjectHydrator.
     *
     * {@inheritdoc}
     */
    protected function hydrateRowData(array $sqlResult, array &$result)
    {
        $entityName = $this->class->name;
        $data = array();

        // We need to find the correct entity class name if we have inheritance in resultset
        if ($this->class->inheritanceType !== ClassMetadata::INHERITANCE_TYPE_NONE) {
            $discrColumnName = $this->_platform->getSQLResultCasing($this->class->discriminatorColumn['name']);

            // Find mapped discriminator column from the result set.
            if ($metaMappingDiscrColumnName = array_search($discrColumnName, $this->_rsm->metaMappings)) {
                $discrColumnName = $metaMappingDiscrColumnName;
            }

            if (!isset($sqlResult[$discrColumnName])) {
                throw HydrationException::missingDiscriminatorColumn($entityName, $discrColumnName, key($this->_rsm->aliasMap));
            }

            if ($sqlResult[$discrColumnName] === '') {
                throw HydrationException::emptyDiscriminatorValue(key($this->_rsm->aliasMap));
            }

            $discrMap = $this->class->discriminatorMap;

            if (!isset($discrMap[$sqlResult[$discrColumnName]])) {
                throw HydrationException::invalidDiscriminatorValue($sqlResult[$discrColumnName], array_keys($discrMap));
            }

            $entityName = $discrMap[$sqlResult[$discrColumnName]];

            unset($sqlResult[$discrColumnName]);
        }

        foreach ($sqlResult as $column => $value) {
            // An ObjectHydrator should be used instead of SimpleObjectHydrator
            if (isset($this->_rsm->relationMap[$column])) {
                throw new \Exception(sprintf('Unable to retrieve association information for column "%s"', $column));
            }

            $cacheKeyInfo = $this->hydrateColumnInfo($column);

            if (!$cacheKeyInfo) {
                continue;
            }

            // Convert field to a valid PHP value
            if (isset($cacheKeyInfo['type'])) {
                $type = $cacheKeyInfo['type'];
                // JSON Document support here
                if ($type->getName() === 'json_document') {
                    $value = [
                        'value' => $value,
                        'class' => $this->class->name,
                        'property' => $cacheKeyInfo['fieldName'],
                    ];
                }
                $value = $type->convertToPHPValue($value, $this->_platform);
            }

            $fieldName = $cacheKeyInfo['fieldName'];

            // Prevent overwrite in case of inherit classes using same property name (See AbstractHydrator)
            if (!isset($data[$fieldName]) || $value !== null) {
                $data[$fieldName] = $value;
            }
        }

        if (isset($this->_hints[Query::HINT_REFRESH_ENTITY])) {
            $this->registerManaged($this->class, $this->_hints[Query::HINT_REFRESH_ENTITY], $data);
        }

        $uow = $this->_em->getUnitOfWork();
        $entity = $uow->createEntity($entityName, $data, $this->_hints);

        $result[] = $entity;

        if (isset($this->_hints[Query::HINT_INTERNAL_ITERATION]) && $this->_hints[Query::HINT_INTERNAL_ITERATION]) {
            $this->_uow->hydrationComplete();
        }
    }
}
