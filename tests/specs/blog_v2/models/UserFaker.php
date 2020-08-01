<?php

namespace app\models;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;

/**
 * Fake data generator for User
 */
class UserFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new User();
        $model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->login = $faker->userName;
        $model->email = $faker->safeEmail;
        $model->password = $faker->password;
        $model->role = $faker->randomElement(['admin', 'editor', 'reader']);
        $model->created_at = $faker->dateTimeThisCentury->format('Y-m-d H:i:s');
        return $model;
    }
}
