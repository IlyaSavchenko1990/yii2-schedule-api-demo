<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Meetings extends ActiveRecord
{

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['date_in', 'date_end',], 'required', 'message' => 'Не задана дата'],
            [['date_in', 'date_end',], 'datetime', 'format' => 'php:Y-m-d H:i', 'message' => 'Некорректный формат даты']
        ];
    }

    public static function getOptimal($dateIn, $dateEnd)
    {
        $meetings = Meetings::find()
            ->where(
                ['>=', 'date_in', $dateIn]
            )
            ->andWhere(
                ['<=', 'date_end', $dateEnd]
            )
            ->orderBy('date_in ASC')
            ->all();

        if (empty($meetings)) {
            return [];
        }

        $cache = [];
        foreach ($meetings as $i => $meeting) {
            if (!isset($cache[$meeting->id])) {
                $cache[$meeting->id] = [
                    'list' => []
                ];
            }

            $din = $meeting->date_in;
            $dend = $meeting->date_end;

            $list = [];
            foreach ($meetings as $k => $innerMeet) {
                if ($innerMeet->id === $meeting->id) {
                    continue;
                }
                if ($innerMeet->date_in >= $dend) {
                    break;
                }

                if ($innerMeet->date_end > $din && $innerMeet->date_in < $dend) {
                    $list[] = $innerMeet->id;
                }
            }

            $cache[$meeting->id]['list'] = $list;
        }

        $ids = array_keys($cache);
        $res = [];
        $processed = [];
        foreach ($ids as $i => $id) {
            if (in_array($id, $res) || in_array($id, $processed)) {
                continue;
            }

            $idToSet = $id;
            $count = count($cache[$id]['list']);
            foreach ($cache[$id]['list'] as $k => $innderId) {
                if (in_array($innderId, $res) || in_array($innderId, $processed)) {
                    continue;
                }

                $innerCount = count($cache[$innderId]['list']);
                if ($innerCount >= $count) {
                    continue;
                }

                $idToSet = $innderId;
                $count = $innerCount;
            }

            $processed[] = $id;
            $processed = array_merge($processed, $cache[$idToSet]['list']);
            $res[] = $idToSet;
        }

        return [
            'list' => $res,
            'ids' => $cache,
            'meets' => $meetings
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert === true || (!$this->isFieldChanged('date_in') && !$this->isFieldChanged('date_end'))) {
            return true;
        }

        try {
            $oldModel = Meetings::findOne($this->id);
            $this->countWeights($oldModel, 'decrement');
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (
            $insert === false
            && !array_key_exists('date_in', $changedAttributes) && !array_key_exists('date_end', $changedAttributes)
        ) {
            return;
        }

        $this->countWeights($this, 'increment');
    }

    private function isFieldChanged($fieldName)
    {
        return (isset($this->getDirtyAttributes()[$fieldName]));
    }

    private function countWeights($model, $action = 'increment')
    {
        if (empty($model->date_in) || empty($model->date_end)) {
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {

            $table = Meetings::tableName();

            $queryModel = Meetings::findBySql("SELECT * FROM $table WHERE id = $model->id FOR UPDATE");
            $queryModel->all();

            $where = "WHERE id != $model->id AND date_end > '$model->date_in' AND date_in < '$model->date_end'";

            $query = Meetings::findBySql("SELECT * FROM $table $where FOR UPDATE");
            $meetings = $query->all();

            if (in_array($action, ['increment', 'decrement'])) {
                $actionSymbol = $action === 'increment' ? '+' : '-';
                Yii::$app->db->createCommand("UPDATE $table SET weight = weight $actionSymbol 1 $where")->queryAll();
            }

            $count = count($meetings);
            if ($count >= 0) {
                Yii::$app->db->createCommand("UPDATE $table SET weight = $count WHERE id = $model->id")->queryAll();
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
