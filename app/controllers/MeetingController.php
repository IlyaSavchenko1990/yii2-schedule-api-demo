<?php

namespace app\controllers;

use app\models\Meetings;
use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class MeetingController extends ActiveController
{

    public $modelClass = Meetings::class;

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update']);
        unset($actions['index']);

        return $actions;
    }

    public function actionCreate()
    {
        $form = Yii::$app->request->post();
        $user = new Meetings();
        $user->load($form, '');
        if (!$user->save()) {
            Yii::$app->response->statusCode = 400;
            return ['errors' => $user->errors];
        }

        return $user;
    }

    public function actionUpdate($id)
    {
        $meeting = Meetings::findOne($id);
        if (empty($meeting)) {
            throw new NotFoundHttpException('Данные о собрании не найдены');
        }

        $form = Yii::$app->request->post();
        $meeting->load($form, '');
        if (!$meeting->save()) {
            Yii::$app->response->statusCode = 400;
            return ['errors' => $meeting->errors];
        }

        return $meeting;
    }

    public function actionIndex()
    {
        $list = Meetings::getOptimal('2023-09-01 00:00', '2023-09-15 23:59');
        return $list;
        $orderBy = Yii::$app->request->get('order_by');
        if (empty($orderBy)) {
            $orderBy = 'id DESC';
        }
        $query = Meetings::find()
            ->orderBy($orderBy);

        $name = Yii::$app->request->get('name');
        if (!empty($name) && is_string($name)) {
            explode($name, ',');
            $query->where(['name' => $name]);
        }

        $limit = intval(Yii::$app->request->get('per-page'));
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit ?: 10
            ]
        ]);

        return $provider;
    }
}
