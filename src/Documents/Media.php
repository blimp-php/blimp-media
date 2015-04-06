<?php
namespace Blimp\Media\Documents;

use Blimp\DataAccess\Documents\BlimpDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;

/** @ODM\Document */
class Media extends BlimpDocument {
    /**
     * @ODM\String
     * @Gedmo\Translatable
     */
    protected $label;

    /** @ODM\String */
    protected $basePath;

    /** @ODM\String */
    protected $relativePath;

    /** @Gedmo\Locale */
    private $locale;

    public function setLabel($label) {
        $this->label = $label;
    }
    public function getLabel() {
        return $this->label;
    }

    public function setBasePath($basePath) {
        $this->basePath = $basePath;
    }
    public function getBasePath() {
        return $this->basePath;
    }

    public function setRelativePath($relativePath) {
        $this->relativePath = $relativePath;
    }
    public function getRelativePath() {
        return $this->relativePath;
    }

    public function setTranslatableLocale($locale) {
        $this->locale = $locale;
    }

    public function toStdClass() {
        return {
            'label' => $this->getLabel(),
            'url' => $this->getId()
        };
    }
}
