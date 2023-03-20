<?php
namespace app\models\fakers;

use Faker\UniqueGenerator;
use app\models\Domain;

/**
 * Fake data generator for Domain
 * @method static \app\models\Domain makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\Domain saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static \app\models\Domain[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static \app\models\Domain[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class DomainFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return \app\models\Domain|\yii\db\ActiveRecord
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
        $model = new \app\models\Domain();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->name = $faker->domainName;
        $model->account_id = $faker->randomElement(\app\models\Account::find()->select("id")->column());
        $model->created_at = $faker->dateTimeThisYear('now', 'UTC')->format('Y-m-d H:i:s');
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
