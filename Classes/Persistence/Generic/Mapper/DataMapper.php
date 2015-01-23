<?php
namespace T3easy\Faltranslation\Persistence\Generic\Mapper;

class DataMapper extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper {

	/**
	 * Builds and returns the prepared query, ready to be executed.
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject
	 * @param string $propertyName
	 * @param string $fieldValue
	 * @param bool $forceTranslationOverlay
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
	 */
	protected function getPreparedQuery(\TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject, $propertyName, $fieldValue = '', $forceTranslationOverlay) {
		$columnMap = $this->getDataMap(get_class($parentObject))->getColumnMap($propertyName);
		$type = $this->getType(get_class($parentObject), $propertyName);
		$query = $this->queryFactory->create($type);
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		if ($columnMap->getTypeOfRelation() === \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap::RELATION_HAS_MANY) {
			if ($columnMap->getChildSortByFieldName() !== NULL) {
				$query->setOrderings(array($columnMap->getChildSortByFieldName() => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
			}
		} elseif ($columnMap->getTypeOfRelation() === \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap::RELATION_HAS_AND_BELONGS_TO_MANY) {
			$query->setSource($this->getSource($parentObject, $propertyName));
			if ($columnMap->getChildSortByFieldName() !== NULL) {
				$query->setOrderings(array($columnMap->getChildSortByFieldName() => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
			}
		}
		$query->matching($this->getConstraint($query, $parentObject, $propertyName, $fieldValue, $columnMap->getRelationTableMatchFields(), $forceTranslationOverlay));
		return $query;
	}

	/**
	 * Builds and returns the constraint for multi value properties.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject
	 * @param string $propertyName
	 * @param string $fieldValue
	 * @param array $relationTableMatchFields
	 * @param bool $forceTranslationOverlay
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint
	 */
	protected function getConstraint(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query, \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject, $propertyName, $fieldValue = '', $relationTableMatchFields = array(), $forceTranslationOverlay) {
		$columnMap = $this->getDataMap(get_class($parentObject))->getColumnMap($propertyName);
		if ($columnMap->getParentKeyFieldName() !== NULL) {
			if ($forceTranslationOverlay || $columnMap->isRelationsOverriddenByTranslation()) {
				$relatedTranslations = 0;
				if (!$forceTranslationOverlay) {
					$relatedTranslations = $this->countRelated($parentObject, $propertyName, $fieldValue, TRUE);
				}
				if ($relatedTranslations > 0 || $forceTranslationOverlay) {
					$constraint = $query->equals($columnMap->getParentKeyFieldName(), $parentObject->_getProperty('_localizedUid'));
				} else {
					$constraint = $query->equals($columnMap->getParentKeyFieldName(), $parentObject);
				}
			} else {
				$constraint = $query->equals($columnMap->getParentKeyFieldName(), $parentObject);
			}
			if ($columnMap->getParentTableFieldName() !== NULL) {
				$constraint = $query->logicalAnd($constraint, $query->equals($columnMap->getParentTableFieldName(), $this->getDataMap(get_class($parentObject))->getTableName()));
			}
		} else {
			$constraint = $query->in('uid', \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $fieldValue));
		}
		if (count($relationTableMatchFields) > 0) {
			foreach ($relationTableMatchFields as $relationTableMatchFieldName => $relationTableMatchFieldValue) {
				$constraint = $query->logicalAnd($constraint, $query->equals($relationTableMatchFieldName, $relationTableMatchFieldValue));
			}
		}
		return $constraint;
	}

	/**
	 * Counts the number of related objects assigned to a property of a parent object
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject The object instance this proxy is part of
	 * @param string $propertyName The name of the proxied property in it's parent
	 * @param mixed $fieldValue The raw field value.
	 * @param bool $forceTranslationOverlay
	 * @return integer
	 */
	public function countRelated(\TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject, $propertyName, $fieldValue = '', $forceTranslationOverlay = FALSE) {
		$query = $this->getPreparedQuery($parentObject, $propertyName, $fieldValue, $forceTranslationOverlay);
		return $query->execute()->count();
	}

}
