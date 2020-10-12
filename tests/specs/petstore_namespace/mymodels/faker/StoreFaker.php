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
        $model = new Store();
        //$model->id = $uniqueFaker->numberBetween(0, 2147483647);
        $model->name = $faker->sentence;
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
        $model->setAttributes($attributes);
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
