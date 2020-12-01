<?php

namespace app\models\base;

/**
 *
 *
 * @property int $id
 * @property int $attach_id
 * @property int $target_id
 *
 * @property \app\models\Photo $attach
 * @property \app\models\Post $target
 */
abstract class PostsAttaches extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%posts_attaches}}';
    }

    public function rules()
    {
        return [
            'attach_id_integer' => [['attach_id'], 'integer'],
            'attach_id_exist' => [['attach_id'], 'exist', 'targetRelation' => 'Attach'],
            'target_id_integer' => [['target_id'], 'integer'],
            'target_id_exist' => [['target_id'], 'exist', 'targetRelation' => 'Target'],
        ];
    }

    public function getAttach()
    {
        return $this->hasOne(\app\models\Photo::class, ['id' => 'attach_id']);
    }

    public function getTarget()
    {
        return $this->hasOne(\app\models\Post::class, ['id' => 'target_id']);
    }
}
