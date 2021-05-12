<?php

namespace app\mymodels\faker;

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

    abstract public function generateModel();

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
     * @param array|callable $attributes
     * @param bool  $save
     * @return \yii\db\ActiveRecord
     * @example MyFaker::makeOne(['user_id' => 1, 'title' => 'foo']);
     * @example MyFaker::makeOne( function($model, $faker) {
     *        $model->scenario = 'create';
     *        $model->setAttributes(['user_id' => 1, 'title' => $faker->sentence]);
     *        return $model;
     *  }, true);
     */
    public static function makeOne($attributes = [], bool $save = false)
    {
        $fakeBuilder = new static();
        $model = $fakeBuilder->generateModel();
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $fakeBuilder->getFaker(), $fakeBuilder->getUniqueFaker());
        }

        if ($save === true) {
            $model->save();
        }
        return $model;
    }

    /**
     * @param int $number
     * @param array|callable $commonAttributes
     * @param bool  $save
     * @return \yii\db\ActiveRecord[]|array
     * @example TaskFaker::make(5, ['project_id'=>1, 'user_id' => 2]);
     * @example TaskFaker::make(5, function($model, $faker, $uniqueFaker) {
     *       $model->setAttributes(['name' => $uniqueFaker->username, 'state'=>$faker->boolean(20)]);
     *       return $model;
     * });
     */
    public static function make(int $number, $commonAttributes = [], bool $save = false):array
    {
        if ($number < 1) {
            return [];
        }
        $fakeBuilder = new static();
        return array_map(function () use ($commonAttributes, $save, $fakeBuilder) {
            $model = $fakeBuilder->generateModel();
            if (!is_callable($commonAttributes)) {
                $model->setAttributes($commonAttributes, false);
            } else {
                $model = $commonAttributes($model, $fakeBuilder->getFaker(), $fakeBuilder->getUniqueFaker());
            }
            if ($save === true) {
                $model->save();
            }
            return $model;
        }, range(0, $number -1));
    }
}
