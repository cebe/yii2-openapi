<?php
namespace app\models\fakers;

use Faker\UniqueGenerator;
use app\models\B123;

/**
 * Fake data generator for B123
 * @method static \app\models\B123 makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\B123 saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\B123[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\B123[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class B123Faker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\B123|\yii\db\ActiveRecord
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
        $model = new \app\models\B123();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->c123_id = $faker->randomElement(\app\models\C123::find()->select("id")->column());
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
            'C123',

        ];
    }
}
