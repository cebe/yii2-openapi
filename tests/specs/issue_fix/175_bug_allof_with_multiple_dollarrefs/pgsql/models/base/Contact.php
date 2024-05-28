<?php

namespace app\models\base;

/**
 * Contact
 *
 * @property int $id
 * @property int $account_id Account
 * @property bool $active
 * @property string $nickname
 *
 * @property \app\models\Account $account
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
            'required' => [['account_id'], 'required'],
            'account_id_integer' => [['account_id'], 'integer'],
            'account_id_exist' => [['account_id'], 'exist', 'targetRelation' => 'Account'],
            'active_boolean' => [['active'], 'boolean'],
            'active_default' => [['active'], 'default', 'value' => false],
            'nickname_string' => [['nickname'], 'string'],
        ];
    }

    public function getAccount()
    {
        return $this->hasOne(\app\models\Account::class, ['id' => 'account_id']);
    }
}
