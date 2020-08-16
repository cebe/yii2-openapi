<?php
/**
 * @var \cebe\yii2openapi\lib\items\DbModel $model
 * @var string $namespace
 * @var string $modelNamespace
 **/
?>
<?= '<?php' ?>


namespace <?= $namespace ?>;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;
<?php if ($modelNamespace !== $namespace): ?>
use <?= $modelNamespace ?>\<?= $model->getClassName() ?>;
<?php endif; ?>

/**
 * Fake data generator for <?= $model->getClassName() ?>

 */
class <?= $model->getClassName() ?>Faker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new <?= $model->getClassName() ?>();
<?php foreach ($model->attributes as $attribute):
        if (!$attribute->fakerStub || $attribute->isReference()) {
            continue;
        } ?>
        $model-><?= $attribute->columnName ?> = <?= $attribute->fakerStub ?>;
<?php endforeach; ?><?php /** For foreign referenced
<?php foreach ($model->attributes as $attribute):
        if (!$attribute->fakerStub || !$attribute->isReference()) {
             continue;
        } ?>
        $model-><?= $attribute->columnName ?> = <?= $attribute->fakerStub ?>;
<?php endforeach; ?>
 **/?>
        return $model;
    }
}
