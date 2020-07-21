<?php

use yii\helpers\FileHelper;

/**
 *
 *
 */
class GeneratorTest extends \PHPUnit\Framework\TestCase
{
    protected function createGenerator($configFile)
    {
        $config = require $configFile;
        return new \cebe\yii2openapi\generator\ApiGenerator($config);
    }

    public function provideTestcases()
    {
        $tests = FileHelper::findFiles(Yii::getAlias('@specs'), ['recursive' => false, 'only' => ['*.php']]);
        $ret = [];
        foreach($tests as $testFile) {
            $ret[] = [substr($testFile, strlen(Yii::getAlias('@specs')) + 1)];
        }
        return $ret;
    }

    /**
     * @dataProvider provideTestcases
     */
    public function testGenerate($testFile)
    {
        $testFile = Yii::getAlias("@specs/$testFile");
        FileHelper::removeDirectory(__DIR__ . '/tmp');
        FileHelper::createDirectory(__DIR__ . '/tmp');
        Yii::setAlias('@app', __DIR__ . '/tmp');

        $app = new \yii\web\Application([
            'id' => 'yii2-openapi-test',
            'basePath' => __DIR__ . '/tmp',
        ]);

        $generator = $this->createGenerator($testFile);
        $this->assertTrue($generator->validate(), print_r($generator->getErrors(), true));

        $codeFiles = $generator->generate();
        foreach($codeFiles as $file) {
            $file->save();
        }

        $expectedFiles = array_map(function($file) use ($testFile) {
            return '@app' . substr($file, strlen($testFile) - 4);
        }, FileHelper::findFiles(substr($testFile, 0, -4), ['recursive' => true]));
        $actualFiles = array_map(function($file) use ($testFile) {
            return '@app' . substr($file, strlen(Yii::getAlias('@app')));
        }, FileHelper::findFiles(Yii::getAlias('@app'), ['recursive' => true]));

        sort($expectedFiles);
        sort($actualFiles);
        $this->assertEquals($expectedFiles, $actualFiles);

        foreach($expectedFiles as $file) {
            $expectedFile = str_replace('@app', substr($testFile, 0, -4), $file);
            $actualFile = str_replace('@app', Yii::getAlias('@app'), $file);
            $this->assertFileExists($expectedFile);
            $this->assertFileExists($actualFile);
            $this->assertFileEquals($expectedFile, $actualFile, "Failed asserting that file contents of\n$actualFile\nare equal to file contents of\n$expectedFile");
        }
    }
}
