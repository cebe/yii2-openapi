<?php

namespace cebe\yii2openapi\lib;

class GeneratorResult
{
    /**
     * @var \cebe\yii2openapi\lib\PreparedData
     */
    private $data;

    /**
     * @var array|\yii\gii\CodeFile[]
     */
    private $files;

    /**
     * @param \cebe\yii2openapi\lib\PreparedData $data
     * @param array|\yii\gii\CodeFile[]          $files
     */
    public function __construct(PreparedData $data, array $files) {

        $this->data = $data;
        $this->files = $files;
    }

    /**
     * @return \cebe\yii2openapi\lib\PreparedData
     */
    public function getData():PreparedData
    {
        return $this->data;
    }

    /**
     * @return array|\yii\gii\CodeFile[]
     */
    public function getFiles():array
    {
        return $this->files;
    }
}
