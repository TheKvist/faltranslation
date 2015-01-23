<?php
namespace T3easy\Faltranslation\Domain\Model;

class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference {

	/**
	 * @return \TYPO3\CMS\Core\Resource\FileReference
	 */
	public function getOriginalResource() {
		if ($this->originalResource === NULL) {
			$this->originalResource = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->getFileReferenceObject($this->_localizedUid);
		}

		return $this->originalResource;
	}
}
