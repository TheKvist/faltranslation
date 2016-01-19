<?php
namespace T3easy\Faltranslation\Persistence\Generic\Mapper;

class DataMapFactory extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory
{
    /**
     * This method tries to determine the type of type of relation to other tables and sets it based on
     * the $TCA column configuration
     *
     * @param ColumnMap $columnMap The column map
     * @param string $columnConfiguration The column configuration from $TCA
     * @param array $propertyMetaData The property metadata as delivered by the reflection service
     * @return ColumnMap
     */
    protected function setRelations(ColumnMap $columnMap, $columnConfiguration, $propertyMetaData)
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

        return $columnMap;
    }
}
