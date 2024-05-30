<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Account
 * @method static Account makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Account saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Account[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Account[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class AccountFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Account|\yii\db\ActiveRecord
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
        $model = new Account();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = substr($faker->text(128), 0, 128);
        $model->paymentMethodName = $faker->sentence;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
