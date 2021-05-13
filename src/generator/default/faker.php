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

use Faker\UniqueGenerator;
<?php if ($modelNamespace !== $namespace): ?>
use <?= $modelNamespace ?>\<?= $model->getClassName() ?>;
<?php endif; ?>

/**
 * Fake data generator for <?= $model->getClassName() ?>

 * @method static <?= $modelClass?> makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static <?= $modelClass?> saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static <?= $modelClass?>[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static <?= $modelClass?>[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class <?= $model->getClassName() ?>Faker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return <?= $modelClass?>|\yii\db\ActiveRecord
     * @example
     *  $model = (new PostFaker())->generateModels(['author_id' => 1]);
     *  $model = (new PostFaker())->generateModels(function($model, $faker, $uniqueFaker) {
     *            $model->scenario = 'create';
     *            $model->author_id = 1;
     *            return $model;
     *  });
    **/
    public function generateModel($attributes = [])
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
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
