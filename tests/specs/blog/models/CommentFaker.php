<?php

namespace app\models;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;

/**
 * Fake data generator for Comment
 */
class CommentFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new Comment();
        $model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->message = [];
        $model->created_at = $faker->unixTime;
        return $model;
    }
}
