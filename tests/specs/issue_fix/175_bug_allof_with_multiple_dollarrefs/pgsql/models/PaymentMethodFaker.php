<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for PaymentMethod
 * @method static PaymentMethod makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static PaymentMethod saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static PaymentMethod[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static PaymentMethod[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PaymentMethodFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return PaymentMethod|\yii\db\ActiveRecord
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
        $model = new PaymentMethod();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
