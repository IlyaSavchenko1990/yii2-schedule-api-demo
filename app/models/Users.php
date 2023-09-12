<?php

namespace app\models;

use yii\db\ActiveRecord;

class Users extends ActiveRecord
{

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name',], 'required', 'message' => 'Не указано имя пользователя']
        ];
    }
}
