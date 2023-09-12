<?php

namespace app\controllers;

use app\models\Users;
use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class UserController extends ActiveController
{

    public $modelClass = Users::class;

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
        $user = new Users();
        $user->load($form, '');
        if (!$user->save()) {
            Yii::$app->response->statusCode = 400;
            return ['errors' => $user->errors];
        }

        return $user;
    }

    public function actionUpdate($id)
    {
        $user = Users::findOne($id);
        if (empty($user)) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        $form = Yii::$app->request->post();
        $user->load($form, '');
        if (!$user->save()) {
            Yii::$app->response->statusCode = 400;
            return ['errors' => $user->errors];
        }

        return $user;
    }

    public function actionIndex()
    {
        $orderBy = Yii::$app->request->get('order_by');
        if (empty($orderBy)) {
            $orderBy = 'id DESC';
        }
        $query = Users::find()
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
