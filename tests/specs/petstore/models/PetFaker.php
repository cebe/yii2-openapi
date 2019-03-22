<?php

namespace app\models;

use Faker\Factory as FakerFactory;

/**
 * Fake data generator for Pet
 */
class PetFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $model = new Pet;
        $model->id = $faker->numberBetween(0, PHP_INT_MAX);
        $model->name = $faker->sentence;
        $model->tag = $faker->randomElement(['one', 'two', 'three', 'four']);
        return $model;
    }
}
