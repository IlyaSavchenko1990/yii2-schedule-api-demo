<?php

namespace app\models;

use DateTime;
use Yii;
use yii\db\ActiveRecord;

class Meetings extends ActiveRecord
{
    const STATUS_CANCELED = 'CANCELED';
    const STATUS_FINISHED = 'FINISHED';
    const STATUS_ACTIVE = 'ACTIVE';

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [
                'state',
                'in',
                'range' => [
                    Meetings::STATUS_ACTIVE,
                    Meetings::STATUS_CANCELED,
                    Meetings::STATUS_FINISHED
                ],
                'message' => 'Указано некорректное состояние'
            ],
            ['state', 'default', 'value' => Meetings::STATUS_ACTIVE],
            [['date_in', 'date_end',], 'required', 'message' => 'Не задана дата'],
            [['date_in', 'date_end',], 'datetime', 'format' => 'php:Y-m-d H:i', 'message' => 'Некорректный формат даты - Y-m-d H:i требуется'],
            ['date_in', 'validateDates']
        ];
    }

    public function validateDates()
    {
        if (strtotime($this->date_end) <= strtotime($this->date_in)) {
            $this->addError('date_in', 'Дата конца собрания не может быть меньше даты начала');
            $this->addError('date_end', 'Дата конца собрания не может быть меньше даты начала');
        }

        $dateIn = DateTime::createFromFormat('Y-m-d H:i', $this->date_in);
        $dateEnd = DateTime::createFromFormat('Y-m-d H:i', $this->date_end);

        if ($dateIn->format('Y-m-d') !== $dateEnd->format('Y-m-d')) {
            $this->addError('date_in', 'Дата начала и конца собрания должны быть заданы в одних сутках');
            $this->addError('date_end', 'Дата начала и конца собрания должны быть заданы в одних сутках');
        }
    }
}
