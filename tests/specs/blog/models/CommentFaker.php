<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for Comment
 * @method static Comment makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Comment saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static Comment[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static Comment[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class CommentFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return Comment|\yii\db\ActiveRecord
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
        $model = new Comment();
        //$model->id = $uniqueFaker->numberBetween(0, 1000000);
        $model->post_id = $faker->randomElement(\app\models\Post::find()->select("id")->column());
        $model->author_id = $faker->randomElement(\app\models\User::find()->select("id")->column());
        $model->message = [];
        $model->meta_data = [];
        $model->created_at = $faker->unixTime;
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
            'Post',
            'User',

        ];
    }
}
