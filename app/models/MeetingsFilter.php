<?php

namespace app\models;

use yii\base\Model;

class MeetingsFilter extends Model
{
    public $title;
    public $date_in;
    public $date_end;
    public $state = Meetings::STATUS_ACTIVE;
    public $order_by = 'date_in ASC';

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['title', 'state'], 'trim'],
            ['state', 'in', 'range' => [Meetings::STATUS_ACTIVE, Meetings::STATUS_CANCELED, Meetings::STATUS_FINISHED]],
            [['date_in', 'date_end',], 'datetime', 'format' => 'php:Y-m-d H:i'],
            [
                'order_by',
                'in',
                'range' => [
                    'id DESC', 'id ASC',
                    'date_in DESC', 'date_in ASC',
                    'date_end DESC', 'date_end ASC',
                    'title DESC', 'title ASC'
                ]
            ]
        ];
    }
}
