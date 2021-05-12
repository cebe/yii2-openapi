<?php
/**
 * @var \cebe\yii2openapi\lib\items\DbModel $model
 * @var string $namespace
 * @var string $modelNamespace
 **/

$modelClass = ($modelNamespace !== $namespace ? '\\'.trim($modelNamespace, '\\').'\\' : '').$model->getClassName();

?>
<?= '<?php' ?>

namespace <?= $namespace ?>;

<?php if ($modelNamespace !== $namespace): ?>
use <?= $modelNamespace ?>\<?= $model->getClassName() ?>;
<?php endif; ?>
/**
 * Fake data generator for <?= $model->getClassName() ?>

 */
class <?= $model->getClassName() ?>Faker extends BaseModelFaker
{

    /**
     * @return <?= $modelClass?>|\yii\db\ActiveRecord
    **/
    public function generateModel()
    {
        $faker = $this->faker;
        $uniqueFaker = $this->uniqueFaker;
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
     * @param array|callable $attributes
     * @param bool  $save
     * @return <?= $modelClass ?>|\yii\db\ActiveRecord
     * @example MyFaker::makeOne(['user_id' => 1, 'title' => 'foo']);
     * @example MyFaker::makeOne( function($model, $faker) {
     *        $model->scenario = 'create';
     *        $model->setAttributes(['user_id' => 1, 'title' => $faker->sentence]);
     *        return $model;
     *  }, true);
     */
    public static function makeOne($attributes = [], bool $save = false)
    {
        return parent::makeOne($attributes, $save);
    }

    /**
     * @param int $number
     * @param array|callable $commonAttributes
     * @param bool  $save
     * @return array|\yii\db\ActiveRecord[]|<?= $modelClass ?>[]
     * @example TaskFaker::make(5, ['project_id'=>1, 'user_id' => 2]);
     * @example TaskFaker::make(5, function($model, $faker, $uniqueFaker) {
     *       $model->setAttributes(['name' => $uniqueFaker->username, 'state'=>$faker->boolean(20)]);
     *       return $model;
     * });
     */
    public static function make(int $number, $commonAttributes = [], bool $save = false):array
    {
        return parent::make($number, $commonAttributes, $save);
    }
}
