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
        $faker = FakerFactory::create(str_replace('-', '_', \Yii::$app->language));
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new <?= $model->getClassName() ?>();
<?php foreach ($model->attributes as $attribute):
        if (!$attribute->fakerStub || $attribute->isReference()) {
            continue;
        }
        ?>
<?php if ($attribute->primary === true && $attribute->phpType === 'int'):?>
        //$model-><?= $attribute->columnName ?> = <?= $attribute->fakerStub ?>;
<?php else:?>
        $model-><?= $attribute->columnName ?> = <?= $attribute->fakerStub ?>;
<?php endif;?>
<?php endforeach; ?>
        return $model;
    }

    /**
     * @param array $attributes
     * @param bool  $save
     * @return \yii\db\ActiveRecordInterface
     */
    public static function makeOne(array $attributes, bool $save = false)
    {
        $model = (new static())->generateModel();
        $model->setAttributes($attributes);
        if ($save === true) {
            $model->save();
        }
        return $model;
    }

    /**
     * @param       $number
     * @param array $commonAttributes
     * @param bool  $save
     * @return \yii\db\ActiveRecordInterface[]|array
     * @example TaskFaker::make(5, ['project_id'=>1, 'user_id' => 2]);
     */
    public static function make($number, array $commonAttributes, bool $save = false):array
    {
        if ($number < 1) {
            return [];
        }
        return array_map(function () use ($commonAttributes, $save) {
            return static::makeOne($commonAttributes, $save);
        }, range(0, $number -1));
    }
}
