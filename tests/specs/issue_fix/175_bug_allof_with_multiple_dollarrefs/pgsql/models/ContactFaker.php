<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Contact
 * @method static Contact makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Contact saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Contact[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Contact[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class ContactFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Contact|\yii\db\ActiveRecord
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
        $model = new Contact();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->account_id = $faker->randomElement(\app\models\Account::find()->select("id")->column());
        $model->active = $faker->boolean;
        $model->nickname = $faker->sentence;
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
