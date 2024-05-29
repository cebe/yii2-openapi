<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Webhook
 * @method static Webhook makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Webhook saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Webhook[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Webhook[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class WebhookFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Webhook|\yii\db\ActiveRecord
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
        $model = new Webhook();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->user_id = $faker->randomElement(\app\models\User::find()->select("id")->column());
        $model->redelivery_of = $faker->randomElement(\app\models\Delivery::find()->select("id")->column());
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
            'User',
            'Delivery',

        ];
    }
}
