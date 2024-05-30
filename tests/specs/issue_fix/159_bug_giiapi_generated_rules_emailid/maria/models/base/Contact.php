<?php

namespace app\models\base;

/**
 * Contact
 *
 * @property int $id
 * @property int $mailing_id Mailing
 * @property bool $active
 * @property string $nickname
 *
 * @property \app\models\Mailing $mailing
 */
abstract class Contact extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%contacts}}';
    }

    public function rules()
    {
        return [
            'trim' => [['nickname'], 'trim'],
            'required' => [['mailing_id'], 'required'],
            'mailing_id_integer' => [['mailing_id'], 'integer'],
            'mailing_id_exist' => [['mailing_id'], 'exist', 'targetRelation' => 'Mailing'],
            'active_boolean' => [['active'], 'boolean'],
            'active_default' => [['active'], 'default', 'value' => false],
            'nickname_string' => [['nickname'], 'string'],
        ];
    }

    public function getMailing()
    {
        return $this->hasOne(\app\models\Mailing::class, ['id' => 'mailing_id']);
    }
}
