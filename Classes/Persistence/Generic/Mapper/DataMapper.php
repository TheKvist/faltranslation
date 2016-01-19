<?php
namespace T3easy\Faltranslation\Persistence\Generic\Mapper;

use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence;

class DataMapper extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
{
    /**
     * Builds and returns the constraint for multi value properties.
     *
     * @param Persistence\QueryInterface $query
     * @param DomainObjectInterface $parentObject
     * @param string $propertyName
     * @param string $fieldValue
     * @param array $relationTableMatchFields
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint
     */
    protected function getConstraint(Persistence\QueryInterface $query, DomainObjectInterface $parentObject, $propertyName, $fieldValue = '', $relationTableMatchFields = array())
    {
        $columnMap = $this->getDataMap(get_class($parentObject))->getColumnMap($propertyName);
        if ($columnMap->getParentKeyFieldName() !== null) {
            if ($columnMap->isRelationsOverriddenByTranslation()) {
                $constraint = $query->equals($columnMap->getParentKeyFieldName(), $parentObject->_getProperty('_localizedUid'));
            } else {
                $constraint = $query->equals($columnMap->getParentKeyFieldName(), $parentObject);
            }
            if ($columnMap->getParentTableFieldName() !== null) {
                $constraint = $query->logicalAnd(
                    $constraint,
                    $query->equals($columnMap->getParentTableFieldName(), $this->getDataMap(get_class($parentObject))->getTableName())
                );
            }
        } else {
            $constraint = $query->in('uid', \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $fieldValue));
        }
        if (!empty($relationTableMatchFields)) {
            foreach ($relationTableMatchFields as $relationTableMatchFieldName => $relationTableMatchFieldValue) {
                $constraint = $query->logicalAnd($constraint, $query->equals($relationTableMatchFieldName, $relationTableMatchFieldValue));
            }
        }
        return $constraint;
    }
}
