<?php
namespace T3easy\Faltranslation\Persistence\Generic\Mapper;

class ColumnMap extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap
{

    /**
     * @var bool
     */
    protected $relationsOverriddenByTranslation;

    /**
     * @return bool
     */
    public function isRelationsOverriddenByTranslation()
    {
        return $this->relationsOverriddenByTranslation;
    }

    /**
     * @param bool $relationsOverriddenByTranslation
     */
    public function setRelationsOverriddenByTranslation($relationsOverriddenByTranslation)
    {
        $this->relationsOverriddenByTranslation = $relationsOverriddenByTranslation;
    }
}
