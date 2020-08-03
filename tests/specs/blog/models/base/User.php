<?php

namespace app\models\base;

/**
 * The User
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $created_at
 *
 */
abstract class User extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%users}}';
    }

    public function rules()
    {
        return [
            [['username', 'email', 'password', 'role', 'created_at'], 'trim'],
            [['username', 'email', 'password'], 'required'],
            [['username'], 'unique'],
            [['username'], 'string', 'max' => 200],
            [['email'], 'unique'],
            [['email'], 'string', 'max' => 200],
            [['email'], 'email'],
            [['password'], 'string'],
            [['role'], 'string', 'max' => 20],
            [['created_at'], 'datetime'],
        ];
    }

}
