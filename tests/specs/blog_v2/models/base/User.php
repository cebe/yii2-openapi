<?php

namespace app\models\base;

/**
 * The User
 *
 * @property int $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property int $flags
 * @property string $created_at
 *
 */
abstract class User extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%v2_users}}';
    }

    public function rules()
    {
        return [
            'trim' => [['login', 'email', 'password', 'created_at'], 'trim'],
            'required' => [['login', 'email', 'password'], 'required'],
            'login_unique' => [['login'], 'unique'],
            'email_unique' => [['email'], 'unique'],
            'login_string' => [['login'], 'string'],
            'email_string' => [['email'], 'string'],
            'email_email' => [['email'], 'email'],
            'password_string' => [['password'], 'string'],
            'flags_integer' => [['flags'], 'integer'],
            'created_at_datetime' => [['created_at'], 'datetime'],
        ];
    }
}
