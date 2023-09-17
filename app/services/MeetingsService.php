<?php

namespace app\services;

use app\models\Meetings;
use app\models\MeetingsFilter;
use app\services\interfaces\IMeetingsService;
use yii\data\ActiveDataProvider;

class MeetingsService implements IMeetingsService
{

    public function findMeetings(MeetingsFilter $filter): ActiveDataProvider
    {
        $query = Meetings::find()
            ->orderBy($filter->order_by);

        if (!empty($filter->title)) {
            $title = $filter->title;
            explode($title, ',');
            $query->where(['title' => $title]);
        }
        if (!empty($filter->state)) {
            $query->andWhere(['state' => $filter->state]);
        }
        if (!empty($filter->date_in)) {
            $query->andWhere(['>=', 'date_in', $filter->date_in]);
        }
        if (!empty($filter->date_end)) {
            $query->andWhere(['<=', 'date_end', $filter->date_end]);
        }

        $provider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $provider;
    }
}
