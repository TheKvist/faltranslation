<?php
namespace T3easy\Faltranslation\Persistence\Generic\Mapper;

class DataMapFactory extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory
{
    /**
     * Builds a data map by adding column maps for all the configured columns in the $TCA.
     * It also resolves the type of values the column is holding and the typo of relation the column
     * represents.
     *
     * @param string $className The class name you want to fetch the Data Map for
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidClassException
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap The data map
     */
    protected function buildDataMapInternal($className)
    {
        if (!class_exists($className)) {
            throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidClassException('Could not find class definition for name "' . $className . '". This could be caused by a mis-spelling of the class name in the class definition.');
        }
        $recordType = null;
        $subclasses = [];
        $tableName = $this->resolveTableName($className);
        $columnMapping = [];
        $frameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $classSettings = $frameworkConfiguration['persistence']['classes'][$className];
        if ($classSettings !== null) {
            if (isset($classSettings['subclasses']) && is_array($classSettings['subclasses'])) {
                $subclasses = $this->resolveSubclassesRecursive($frameworkConfiguration['persistence']['classes'], $classSettings['subclasses']);
            }
            if (isset($classSettings['mapping']['recordType']) && $classSettings['mapping']['recordType'] !== '') {
                $recordType = $classSettings['mapping']['recordType'];
            }
            if (isset($classSettings['mapping']['tableName']) && $classSettings['mapping']['tableName'] !== '') {
                $tableName = $classSettings['mapping']['tableName'];
            }
            $classHierarchy = array_merge([$className], class_parents($className));
            foreach ($classHierarchy as $currentClassName) {
                if (in_array($currentClassName, [\TYPO3\CMS\Extbase\DomainObject\AbstractEntity::class, \TYPO3\CMS\Extbase\DomainObject\AbstractValueObject::class])) {
                    break;
                }
                $currentClassSettings = $frameworkConfiguration['persistence']['classes'][$currentClassName];
                if ($currentClassSettings !== null) {
                    if (isset($currentClassSettings['mapping']['columns']) && is_array($currentClassSettings['mapping']['columns'])) {
                        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($columnMapping, $currentClassSettings['mapping']['columns'], true, false);
                    }
                }
            }
        }
        /** @var $dataMap \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap */
        $dataMap = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap::class, $className, $tableName, $recordType, $subclasses);
        $dataMap = $this->addMetaDataColumnNames($dataMap, $tableName);
        // $classPropertyNames = $this->reflectionService->getClassPropertyNames($className);
        $tcaColumnsDefinition = $this->getColumnsDefinition($tableName);
        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($tcaColumnsDefinition, $columnMapping);
        // @todo Is this is too powerful?

        foreach ($tcaColumnsDefinition as $columnName => $columnDefinition) {
            if (isset($columnDefinition['mapOnProperty'])) {
                $propertyName = $columnDefinition['mapOnProperty'];
            } else {
                $propertyName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase($columnName);
            }
            // if (in_array($propertyName, $classPropertyNames)) {
            // @todo Enable check for property existence
            $columnMap = $this->createColumnMap($columnName, $propertyName);
            $propertyMetaData = $this->reflectionService->getClassSchema($className)->getProperty($propertyName);
            $columnMap = $this->setType($columnMap, $columnDefinition['config']);
            $columnMap = $this->setPatchedRelations($columnMap, $columnDefinition['config'], $propertyMetaData);
            $columnMap = $this->setFieldEvaluations($columnMap, $columnDefinition['config']);
            $dataMap->addColumnMap($columnMap);
        }
        return $dataMap;
    }

    /**
     * This method tries to determine the type of type of relation to other tables and sets it based on
     * the $TCA column configuration
     *
     * @param ColumnMap $columnMap The column map
     * @param string $columnConfiguration The column configuration from $TCA
     * @param array $propertyMetaData The property metadata as delivered by the reflection service
     * @return ColumnMap
     */
    protected function setPatchedRelations(ColumnMap $columnMap, $columnConfiguration, $propertyMetaData)
    {
        if (isset($columnConfiguration)) {
            if (isset($columnConfiguration['MM'])) {
                $columnMap = $this->setManyToManyRelation($columnMap, $columnConfiguration);
            } elseif (isset($propertyMetaData['elementType'])) {
                $columnMap = $this->setOneToManyRelation($columnMap, $columnConfiguration);
            } elseif (isset($propertyMetaData['type']) && strpbrk($propertyMetaData['type'], '_\\') !== false) {
                $columnMap = $this->setOneToOneRelation($columnMap, $columnConfiguration);
            } elseif (isset($columnConfiguration['type']) && $columnConfiguration['type'] === 'select' && isset($columnConfiguration['maxitems']) && $columnConfiguration['maxitems'] > 1) {
                $columnMap->setTypeOfRelation(ColumnMap::RELATION_HAS_MANY);
            } else {
                $columnMap->setTypeOfRelation(ColumnMap::RELATION_NONE);
            }
        } else {
            $columnMap->setTypeOfRelation(ColumnMap::RELATION_NONE);
        }

        if (isset($columnConfiguration['behaviour']['localizationMode'])) {
            $columnMap->setRelationsOverriddenByTranslation($columnConfiguration['behaviour']['localizationMode'] !== 'keep');
        }

        if (isset($columnConfiguration['behaviour']['allowLanguageSynchronization'])) {
            $columnMap->setRelationsOverriddenByTranslation($columnConfiguration['behaviour']['allowLanguageSynchronization']);
        }

        return $columnMap;
    }
}
