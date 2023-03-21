<?php
namespace app\models\fakers;

use Faker\UniqueGenerator;
use app\models\A123;

/**
 * Fake data generator for A123
 * @method static \app\models\A123 makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\A123 saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\A123[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\A123[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class A123Faker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\A123|\yii\db\ActiveRecord
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
        $model = new \app\models\A123();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->sentence;
        $model->b123_id = $faker->randomElement(\app\models\B123::find()->select("id")->column());
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
            'B123',

        ];
    }
}
