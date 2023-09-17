<?php

namespace app\services;

use app\models\Users;
use app\models\UsersFilter;
use app\services\interfaces\IUsersService;
use yii\data\ActiveDataProvider;

class UsersService implements IUsersService
{

    public function findUsers(UsersFilter $filter): ActiveDataProvider
    {
        $query = Users::find()
            ->orderBy($filter->order_by);

        if (!empty($filter->name)) {
            $name = $filter->name;
            explode($name, ',');
            $query->where(['name' => $name]);
        }

        $provider = new ActiveDataProvider([
            'query' => $query
        ]);
        
        return $provider;
    }
}
