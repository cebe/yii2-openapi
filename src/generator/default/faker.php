<?= '<?php' ?>


namespace <?= $namespace ?>;

use Faker\Factory as FakerFactory;

/**
 * Fake data generator for <?= $modelClass ?>

 */
class <?= $className ?>

{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
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
