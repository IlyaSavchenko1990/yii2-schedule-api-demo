<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class UserForm extends Model
{
    public string|null $name = null;

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
