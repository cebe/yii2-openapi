<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for E123
 * @method static E123 makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static E123 saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static E123[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static E123[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class E123Faker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return E123|\yii\db\ActiveRecord
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
        $model = new E123();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->account_id = $faker->randomElement(\app\models\Account::find()->select("id")->column());
        $model->account_2_id = $faker->randomElement(\app\models\Account::find()->select("id")->column());
        $model->account_3_id = $faker->randomElement(\app\models\Account::find()->select("id")->column());
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }

    public static function dependentOn()
    {
        return [
            // just model class names
            'Account',

        ];
    }
}
