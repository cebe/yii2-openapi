<?php

namespace cebe\yii2openapi\lib;

use yii\gii\CodeFile;
use function array_merge;

class CodeFiles
{
    /**
     * @var array|\yii\gii\CodeFile[]
     */
    private $files;

    /**
     * @param array|\yii\gii\CodeFile[] $files
     */
    public function __construct(array $files = [])
    {

        $this->files = $files;
    }

    public function add(CodeFile $file):void
    {
        $this->files[] = $file;
    }


    public function merge(CodeFiles $files):void
    {
        $this->files = array_merge($this->files, $files->all());
    }

    /**
     * @return array|\yii\gii\CodeFile[]
     */
    public function all():array
    {
        return $this->files;
    }
}
