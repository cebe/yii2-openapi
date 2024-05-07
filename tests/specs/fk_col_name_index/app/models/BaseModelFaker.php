<?php

namespace app\models;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Faker\UniqueGenerator;

/**
 * Base fake data generator
 */
abstract class BaseModelFaker
{
    /**
     * @var Generator
    */
    protected $faker;
    /**
     * @var UniqueGenerator
    */
    protected $uniqueFaker;

    public function __construct()
    {
        $this->faker = FakerFactory::create(str_replace('-', '_', \Yii::$app->language));
        $this->uniqueFaker = new UniqueGenerator($this->faker);
    }

    abstract public function generateModel($attributes = []);

    public function getFaker():Generator
    {
        return $this->faker;
    }

    public function getUniqueFaker():UniqueGenerator
    {
        return $this->uniqueFaker;
    }

    public function setFaker(Generator $faker):void
    {
        $this->faker = $faker;
    }

    public function setUniqueFaker(UniqueGenerator $faker):void
    {
        $this->uniqueFaker = $faker;
    }

    /**
     * Generate and return model
     * @param array|callable $attributes
     * @param UniqueGenerator|null $uniqueFaker
     * @return \yii\db\ActiveRecord
     * @example MyFaker::makeOne(['user_id' => 1, 'title' => 'foo']);
     * @example MyFaker::makeOne( function($model, $faker) {
     *        $model->scenario = 'create';
     *        $model->setAttributes(['user_id' => 1, 'title' => $faker->sentence]);
     *        return $model;
     *  });
     */
    public static function makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null)
    {
        $fakeBuilder = new static();
        if ($uniqueFaker !== null) {
            $fakeBuilder->setUniqueFaker($uniqueFaker);
        }
        $model = $fakeBuilder->generateModel($attributes);
        return $model;
    }

    /**
     * Generate, save and return model
     * @param array|callable $attributes
     * @param UniqueGenerator|null $uniqueFaker
     * @return \yii\db\ActiveRecord
     * @example MyFaker::saveOne(['user_id' => 1, 'title' => 'foo']);
     * @example MyFaker::saveOne( function($model, $faker) {
     *        $model->scenario = 'create';
     *        $model->setAttributes(['user_id' => 1, 'title' => $faker->sentence]);
     *        return $model;
     *  });
     */
    public static function saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null)
    {
        $model = static::makeOne($attributes, $uniqueFaker);
        $model->save();
        return $model;
    }

    /**
     * Generate and return multiple models
     * @param int $number
     * @param array|callable $commonAttributes
     * @return \yii\db\ActiveRecord[]|array
     * @example TaskFaker::make(5, ['project_id'=>1, 'user_id' => 2]);
     * @example TaskFaker::make(5, function($model, $faker, $uniqueFaker) {
     *       $model->setAttributes(['name' => $uniqueFaker->username, 'state'=>$faker->boolean(20)]);
     *       return $model;
     * });
     */
    public static function make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null):array
    {
        if ($number < 1) {
            return [];
        }
        $fakeBuilder = new static();
        if ($uniqueFaker !== null) {
            $fakeBuilder->setUniqueFaker($uniqueFaker);
        }
        return array_map(function () use ($commonAttributes, $fakeBuilder) {
            $model = $fakeBuilder->generateModel($commonAttributes);
            return $model;
        }, range(0, $number -1));
    }

    /**
     * Generate, save and return multiple models
     * @param int $number
     * @param array|callable $commonAttributes
     * @return \yii\db\ActiveRecord[]|array
     * @example TaskFaker::save(5, ['project_id'=>1, 'user_id' => 2]);
     * @example TaskFaker::save(5, function($model, $faker, $uniqueFaker) {
     *       $model->setAttributes(['name' => $uniqueFaker->username, 'state'=>$faker->boolean(20)]);
     *       return $model;
     * });
     */
    public static function save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null):array
    {
        if ($number < 1) {
            return [];
        }
        $fakeBuilder = new static();
        if ($uniqueFaker !== null) {
            $fakeBuilder->setUniqueFaker($uniqueFaker);
        }
        return array_map(function () use ($commonAttributes, $fakeBuilder) {
            $model = $fakeBuilder->generateModel($commonAttributes);
            $model->save();
            return $model;
        }, range(0, $number -1));
    }
}
