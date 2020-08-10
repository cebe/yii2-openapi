<?php

namespace app\models;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;

/**
 * Fake data generator for Custom
 */
class CustomFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new Custom();
        $model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->num = $faker->numberBetween(0, 2147483647);
        $model->json1 = [];
        $model->json2 = [];
        $model->json3 = [];
        $model->json4 = [];
        $model->status = $faker->randomElement(['draft','pending','active']);
        return $model;
    }
}
