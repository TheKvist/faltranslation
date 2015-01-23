<?php
namespace T3easy\Faltranslation\Persistence\Generic\Mapper;


class ColumnMap extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap {

	/**
	 * @var bool
	 */
	protected $relationsOverriddenByTranslation;

	/**
	 * @return boolean
	 */
	public function isRelationsOverriddenByTranslation() {
		return $this->relationsOverriddenByTranslation;
	}

	/**
	 * @param boolean $relationsOverriddenByTranslation
	 */
	public function setRelationsOverriddenByTranslation($relationsOverriddenByTranslation) {
		$this->relationsOverriddenByTranslation = $relationsOverriddenByTranslation;
	}

}
