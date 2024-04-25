<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Invoice
 * @method static Invoice makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Invoice saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Invoice[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Invoice[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class InvoiceFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Invoice|\yii\db\ActiveRecord
     * @example
     *  $model = (new PostFaker())->generateModels(['author_id' => 1]);
     *  $model = (new PostFaker())->generateModels(function($model, $faker, $uniqueFaker) {
     *            $model->scenario = 'create';
     *            $model->author_id = 1;
     *            return $model;
     *  });
    **/
    public function generateModel($attributes = [])
    {
        $faker = $this->faker;
        $uniqueFaker = $this->uniqueFaker;
        $model = new Invoice();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
