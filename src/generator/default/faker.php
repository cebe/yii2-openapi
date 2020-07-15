<?= '<?php' ?>


namespace <?= $namespace ?>;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;
<?php if ($modelNamespace !== $namespace): ?>
use <?= $modelNamespace ?>\<?= $modelClass ?>;
<?php endif; ?>

/**
 * Fake data generator for <?= $modelClass ?>

 */
class <?= $className ?>

{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new <?= $modelClass ?>;
<?php foreach ($attributes as $attribute):
        if (!isset($attribute['faker'])) {
            continue;
        } ?>
        $model-><?= $attribute['name'] ?> = <?= $attribute['faker'] ?>;
<?php endforeach; ?>
        return $model;
    }
}
