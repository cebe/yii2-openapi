<?php

namespace app\models;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;

/**
 * Fake data generator for Tag
 */
class TagFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new Tag();
        $model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->name = substr($faker->text(100), 0, 100);
        $model->lang = $faker->randomElement(array (
  0 => 'ru',
  1 => 'eng',
));
        return $model;
    }
}
