<?php

namespace app\models;

use yii\base\Model;

class UsersFilter extends Model
{
    public $name;
    public $order_by = 'id DESC';


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['name'], 'trim'],
            ['order_by', 'in', 'range' => ['id DESC', 'id ASC', 'name DESC', 'name ASC']]
        ];
    }
}
