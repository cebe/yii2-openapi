<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Mailing
 * @method static Mailing makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Mailing saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Mailing[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Mailing[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class MailingFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Mailing|\yii\db\ActiveRecord
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
        $model = new Mailing();
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
