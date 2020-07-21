<?php

namespace app\mymodels\faker;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;
use app\mymodels\Store;

/**
 * Fake data generator for Store
 */
class StoreFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new Store;
        $model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->name = $faker->sentence;
        return $model;
    }
}
