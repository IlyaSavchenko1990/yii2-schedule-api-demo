<?php

namespace app\controllers;

use app\models\Users;
use app\models\UsersFilter;
use app\services\interfaces\IUsersMeetingsService;
use app\services\interfaces\IUsersService;
use DateTime;
use Yii;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class UserController extends ActiveController
{
    protected IUsersMeetingsService $usersMeetingsService;
    protected IUsersService $usersService;

    public function __construct(
        $id,
        $module,
        IUsersMeetingsService $usersMeetingsService,
        IUsersService $usersService,
        $config = []
    ) {
        $this->usersMeetingsService = $usersMeetingsService;
        $this->usersService = $usersService;
        parent::__construct($id, $module, $config);
    }

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
        unset($actions['delete']);
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

    public function actionDelete($id)
    {
        $user = Users::findOne($id);
        if (empty($user)) {
            throw new NotFoundHttpException('Данные пользователя не найдены');
        }

        return Users::deleteAll(['id' => $id]);
    }

    public function actionAttach($id)
    {
        $form = Yii::$app->request->post();
        if (!isset($form['meetings_ids'])) {
            throw new BadRequestHttpException('Не заданы собрания');
        }

        $meetingsIds = explode(',', $form['meetings_ids']);

        return $this->usersMeetingsService->attachUserToMeetings($id, $meetingsIds);
    }

    public function actionDetach($id)
    {
        $form = Yii::$app->request->post();
        if (!isset($form['meetings_ids'])) {
            throw new BadRequestHttpException('Не заданы собрания');
        }

        $meetingsIds = explode(',', $form['meetings_ids']);

        return $this->usersMeetingsService->detachUserFromMeetings($id, $meetingsIds);
    }

    public function actionIndex()
    {
        $filter = new UsersFilter();
        $filter->load(Yii::$app->request->getQueryParams(), '');
        if (!$filter->validate()) {
            Yii::$app->response->statusCode = 400;
            return ['errors' => $filter->errors];
        }

        return $this->usersService->findUsers($filter);
    }

    /**
     * Возвращает оптимальное расписание собраний для заданного пользователя
     * на заданную дату
     */
    public function actionSchedule($id)
    {
        $user = Users::findOne($id);
        if (empty($user)) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        $query = Yii::$app->request->queryParams;

        $dateIn = null;
        if (!isset($query['date_in'])) {
            $now = new DateTime('now');
            $now->setTime(8, 0);
            $dateIn = $now->format('Y-m-d H:i');
        } else {
            $dateIn = $query['date_in'];
        }

        $dateEnd = null;
        if (!isset($query['date_end'])) {
            $now = new DateTime('now');
            $now->setTime(18, 0);
            $dateEnd = $now->format('Y-m-d H:i');
        } else {
            $dateEnd = $query['date_end'];
        }

        $list = $this->usersMeetingsService->getScheduleForUser($id, $dateIn, $dateEnd);

        return $list;
    }
}
