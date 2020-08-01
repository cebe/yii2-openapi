<?php

namespace app\models;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;

/**
 * Fake data generator for Category
 */
class CategoryFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new Category();
        $model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->title = substr($faker->sentence, 0, 255);
        $model->active = $faker->boolean;
        return $model;
    }
}
