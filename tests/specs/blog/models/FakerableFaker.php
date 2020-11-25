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
        $faker = FakerFactory::create(str_replace('-', '_', \Yii::$app->language));
        $uniqueFaker = new UniqueGenerator($faker);
        $model = new Fakerable();
        //$model->id = $uniqueFaker->numberBetween(0, 2147483647);
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

    /**
     * @param array $attributes
     * @param bool  $save
     * @return \yii\db\ActiveRecordInterface
     */
    public static function makeOne(array $attributes, bool $save = false)
    {
        $model = (new static())->generateModel();
        $model->setAttributes($attributes, false);
        if ($save === true) {
            $model->save();
        }
        return $model;
    }

    /**
     * @param       $number
     * @param array $commonAttributes
     * @param bool  $save
     * @return \yii\db\ActiveRecordInterface[]|array
     * @example TaskFaker::make(5, ['project_id'=>1, 'user_id' => 2]);
     */
    public static function make($number, array $commonAttributes, bool $save = false):array
    {
        if ($number < 1) {
            return [];
        }
        return array_map(function () use ($commonAttributes, $save) {
            return static::makeOne($commonAttributes, $save);
        }, range(0, $number -1));
    }
}
