<?php

namespace app\models\base;

/**
 * desc
 *
 * @property int $id
 * @property string $name
 * @property int $account_id user account
 * @property int $account_2_id user account
 * @property int $account_3_id user account
 *
 * @property \app\models\Account $account
 * @property \app\models\Account $account2
 * @property \app\models\Account $account3
 */
abstract class E123 extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%e123s}}';
    }

    public function rules()
    {
        return [
            'trim' => [['name'], 'trim'],
            'account_id_integer' => [['account_id'], 'integer'],
            'account_id_exist' => [['account_id'], 'exist', 'targetRelation' => 'Account'],
            'account_2_id_integer' => [['account_2_id'], 'integer'],
            'account_2_id_exist' => [['account_2_id'], 'exist', 'targetRelation' => 'Account2'],
            'account_3_id_integer' => [['account_3_id'], 'integer'],
            'account_3_id_exist' => [['account_3_id'], 'exist', 'targetRelation' => 'Account3'],
            'name_string' => [['name'], 'string'],
        ];
    }

    public function getAccount()
    {
        return $this->hasOne(\app\models\Account::class, ['id' => 'account_id']);
    }

    public function getAccount2()
    {
        return $this->hasOne(\app\models\Account::class, ['id' => 'account_2_id']);
    }

    public function getAccount3()
    {
        return $this->hasOne(\app\models\Account::class, ['id' => 'account_3_id']);
    }
}
