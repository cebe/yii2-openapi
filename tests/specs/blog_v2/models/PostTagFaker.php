<?php

namespace app\models;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;

/**
 * Fake data generator for PostTag
 */
class PostTagFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new PostTag();
        $model->id = $uniqueFaker->numberBetween(0, 2147483647);
        return $model;
    }
}
