<?php

namespace app\controllers;

use app\models\Meetings;
use app\models\MeetingsFilter;
use app\services\interfaces\IMeetingsService;
use app\services\interfaces\IUsersMeetingsService;
use Yii;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class MeetingController extends ActiveController
{

    protected IUsersMeetingsService $usersMeetingsService;
    protected IMeetingsService $meetingsService;

    public function __construct(
        $id,
        $module,
        IUsersMeetingsService $usersMeetingsService,
        IMeetingsService $meetingsService,
        $config = []
    ) {
        $this->usersMeetingsService = $usersMeetingsService;
        $this->meetingsService = $meetingsService;
        parent::__construct($id, $module, $config);
    }

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
        $meeting = new Meetings();
        $meeting->load($form, '');
        if (!$meeting->save()) {
            Yii::$app->response->statusCode = 400;
            return ['errors' => $meeting->errors];
        }

        return $meeting;
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

    public function actionAttach($id)
    {
        $form = Yii::$app->request->post();
        if (!isset($form['users_ids'])) {
            throw new BadRequestHttpException('Не заданы пользователи');
        }

        $usersIds = explode(',', $form['users_ids']);

        return $this->usersMeetingsService->attachUsersToMeeting($id, $usersIds);
    }

    public function actionDetach($id)
    {
        $form = Yii::$app->request->post();
        if (!isset($form['users_ids'])) {
            throw new BadRequestHttpException('Не заданы пользователи');
        }

        $usersIds = explode(',', $form['users_ids']);

        return $this->usersMeetingsService->detachUsersFromMeeting($id, $usersIds);
    }

    public function actionIndex()
    {
        $filter = new MeetingsFilter();
        $filter->load(Yii::$app->request->getQueryParams(), '');
        if (!$filter->validate()) {
            Yii::$app->response->statusCode = 400;
            return ['errors' => $filter->errors];
        }

        return $this->meetingsService->findMeetings($filter);
    }
}
