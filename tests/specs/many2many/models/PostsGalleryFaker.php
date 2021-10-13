<?php
namespace app\models;

use Faker\UniqueGenerator;

/**
 * Fake data generator for PostsGallery
 * @method static PostsGallery makeOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static PostsGallery saveOne($attributes = [], ?UniqueGenerator $uniqueFaker = null);
 * @method static PostsGallery[] make(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 * @method static PostsGallery[] save(int $number, $commonAttributes = [], ?UniqueGenerator $uniqueFaker = null)
 */
class PostsGalleryFaker extends BaseModelFaker
{

    /**
     * @param array|callable $attributes
     * @return PostsGallery|\yii\db\ActiveRecord
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
        $model = new PostsGallery();
        $model->is_cover = $faker->boolean;
        if (!is_callable($attributes)) {
            $model->setAttributes($attributes, false);
        } else {
            $model = $attributes($model, $faker, $uniqueFaker);
        }
        return $model;
    }
}
