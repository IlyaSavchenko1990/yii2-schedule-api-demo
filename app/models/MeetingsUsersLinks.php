<?php

namespace app\models;

use yii\db\ActiveRecord;

class MeetingsUsersLinks extends ActiveRecord
{

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['user_id'], 'required', 'message' => 'Не указан идентификатор пользователя'],
            [['meeting_id'], 'required', 'message' => 'Не указан идентификатор собрания']
        ];
    }
}
