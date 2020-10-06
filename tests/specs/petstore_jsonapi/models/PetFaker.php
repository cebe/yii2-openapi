<?php

namespace app\models;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;

/**
 * Fake data generator for Pet
 */
class PetFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new Pet();
        $model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->name = $faker->sentence;
        $model->tag = $faker->randomElement(['one', 'two', 'three', 'four']);
        return $model;
    }
}
