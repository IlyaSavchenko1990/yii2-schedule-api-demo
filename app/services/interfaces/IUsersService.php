<?php

namespace app\services\interfaces;

use app\models\UsersFilter;
use yii\data\ActiveDataProvider;

interface IUsersService
{

    /**
     * Фильтрует массив по алгоритму для посешения максимального кол-ва собраний
     * Сравнивает каждое собрание и определяет кол-во пересечений
     * 
     * @param array Исходный массив собраний
     * @return array Исходный отфильтрованный массив собраний в порядке, обеспечивающим большее кол-во посещений
     */
    public function findUsers(UsersFilter $filter): ActiveDataProvider;
}
