<?php

namespace app\models;

use Faker\Factory as FakerFactory;
use Faker\UniqueGenerator;

/**
 * Fake data generator for Fakerable
 */
class FakerableFaker
{
    public function generateModel()
    {
        $faker = FakerFactory::create(\Yii::$app->language);
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new Fakerable();
        $model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->active = $faker->boolean;
        $model->floatval = $faker->randomFloat();
        $model->floatval_lim = $faker->randomFloat(null, 0, 1);
        $model->doubleval = $faker->randomFloat();
        $model->int_min = $faker->numberBetween(5, 2147483647);
        $model->int_max = $faker->numberBetween(0, 5);
        $model->int_minmax = $faker->numberBetween(5, 25);
        $model->int_created_at = $faker->unixTime;
        $model->int_simple = $faker->numberBetween(0, 2147483647);
        $model->uuid = $faker->uuid;
        $model->str_text = $faker->sentence;
        $model->str_varchar = substr($faker->text(100), 0, 100);
        $model->str_date = $faker->dateTimeThisCentury->format('Y-m-d');
        $model->str_datetime = $faker->dateTimeThisYear('now', 'UTC')->format(DATE_ATOM);
        $model->str_country = $faker->countryCode;
        return $model;
    }
}
