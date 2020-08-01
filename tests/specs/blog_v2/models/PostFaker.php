<?php

namespace app\models;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;

/**
 * Fake data generator for Post
 */
class PostFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new Post();
        $model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->title = substr($faker->sentence, 0, 255);
        $model->slug = substr($uniqueFaker->slug, 0, 200);
        $model->lang = $faker->randomElement(array (
  0 => 'ru',
  1 => 'eng',
));
        $model->active = $faker->boolean;
        $model->created_at = $faker->iso8601;
        return $model;
    }
}
