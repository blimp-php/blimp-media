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
    protected $bucket;

    /** @ODM\String */
    protected $filePath;

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

    public function setBucket($bucket) {
        $this->bucket = $bucket;
    }
    public function getBucket() {
        return $this->bucket;
    }

    public function setFilePath($filePath) {
        $this->filePath = $filePath;
    }
    public function getFilePath() {
        return $this->filePath;
    }

    public function setTranslatableLocale($locale) {
        $this->locale = $locale;
    }

    public function toStdClass($api, $level = 0) {
        $obj = [
            'label' => $this->getLabel(),
            'url' => $api['media.http.path'].'/'.$this->getBucket().'/'.$this->getId()
        ];

        return $obj;
    }
}
