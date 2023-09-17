<?php

namespace app\services;

use app\models\Meetings;
use app\models\MeetingsUsersLinks;
use app\models\Users;
use app\services\interfaces\IUsersMeetingsService;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Yii;
use yii\web\BadRequestHttpException;

class UsersMeetingsService implements IUsersMeetingsService
{

    public function attachUsersToMeeting(int $meetingId, array $usersIds): int
    {
        if (empty($usersIds)) return 0;

        $meeting = Meetings::findOne($meetingId);
        if (empty($meeting) || $meeting->state !== Meetings::STATUS_ACTIVE) {
            throw new BadRequestHttpException('Собрание не найдено или было завершено/отменено');
        }

        $usersIds = array_filter($usersIds, function ($userId) {
            return is_numeric($userId);
        });

        $users = Users::find()
            ->where(['id' => $usersIds])
            ->all();

        if (empty($users)) {
            return 0;
        }

        $values = array_map(function ($user) use ($meetingId) {
            return [$user->id, $meetingId];
        }, $users);

        try {
            $sql = Yii::$app->db
                ->createCommand()
                ->batchInsert(
                    MeetingsUsersLinks::tableName(),
                    ['user_id', 'meeting_id'],
                    $values
                )->sql;

            return Yii::$app->db
                ->createCommand($sql . ' ON DUPLICATE KEY UPDATE id=id')
                ->execute();
        } catch (\Throwable $e) {
            throw new InternalErrorException('Не удалось назначить пользователей на собрание');
        }
    }

    public function detachUsersFromMeeting(int $meetingId, array $usersIds): int
    {
        $usersIds = array_filter($usersIds, function ($userId) {
            return is_numeric($userId);
        });

        if (empty($usersIds)) return 0;

        return MeetingsUsersLinks::deleteAll(
            [
                'and',
                ['meeting_id' => $meetingId],
                ['user_id' => $usersIds]
            ]
        );
    }

    public function attachUserToMeetings(int $userId, array $meetingsIds): int
    {
        if (empty($meetingsIds)) return 0;

        $user = Users::findOne($userId);
        if (empty($user)) {
            throw new BadRequestHttpException('Пользователь не найден');
        }

        $meetingsIds = array_filter($meetingsIds, function ($meetingId) {
            return is_numeric($meetingId);
        });

        $meetings = Meetings::find()
            ->where(['id' => $meetingsIds])
            ->all();

        if (empty($meetings)) {
            return 0;
        }

        $values = array_map(function ($meeting) use ($userId) {
            return [$userId, $meeting->id];
        }, $meetings);

        try {
            $sql = Yii::$app->db
                ->createCommand()
                ->batchInsert(
                    MeetingsUsersLinks::tableName(),
                    ['user_id', 'meeting_id'],
                    $values
                )->sql;

            return Yii::$app->db
                ->createCommand($sql . ' ON DUPLICATE KEY UPDATE id=id')
                ->execute();
        } catch (\Throwable $e) {
            throw new InternalErrorException('Не удалось назначить пользователя на собрания');
        }
    }

    public function detachUserFromMeetings(int $userId, array $meetingsIds): int
    {
        $meetingsIds = array_filter($meetingsIds, function ($userId) {
            return is_numeric($userId);
        });

        if (empty($meetingsIds)) return 0;

        return MeetingsUsersLinks::deleteAll(
            [
                'and',
                ['meeting_id' => $meetingsIds],
                ['user_id' => $userId]
            ]
        );
    }

    public function getScheduleForUser(int $userId, string $dateFrom, string $dateTo): array
    {
        if (strtotime($dateFrom) === false || strtotime($dateTo) === false) {
            return [];
        }

        $meetingsTable = Meetings::tableName();
        $linksTable = MeetingsUsersLinks::tableName();

        $meetings = Meetings::find()
            ->select($meetingsTable . '.*')
            ->innerJoin($linksTable, "$linksTable.meeting_id = $meetingsTable.id")
            ->where(["$linksTable.user_id" => $userId])
            ->andWhere(["$meetingsTable.state" => Meetings::STATUS_ACTIVE])
            ->andWhere(
                ['>=', "$meetingsTable.date_in", $dateFrom]
            )
            ->andWhere(
                ['<=', "$meetingsTable.date_end", $dateTo]
            )
            ->orderBy("$meetingsTable.date_in ASC")
            ->all();

        return $this->filterForMaxMeet($meetings);
    }

    public function filterForMaxMeet($meetings): array
    {
        if (empty($meetings)) {
            return [];
        }

        $heap = []; //Формируем кучу собраний с пересечениями между собой
        foreach ($meetings as $i => $meeting) {
            $din = $meeting->date_in;
            $dend = $meeting->date_end;

            $neighbours = [];
            foreach ($meetings as $k => $innerMeet) {
                if ($innerMeet->id === $meeting->id) {
                    continue;
                }
                if ($innerMeet->date_in >= $dend) {
                    //Если не пересекаются
                    break;
                }

                if ($innerMeet->date_end > $din && $innerMeet->date_in < $dend) {
                    $neighbours[] = $innerMeet->id;
                }
            }

            $heap[$meeting->id] = [
                'item' => $meeting,
                'neighbours' => $neighbours
            ];
        }

        $ids = array_keys($heap);
        $result = [];
        $processed = [];

        //Считаем кол-во соседей/пересечений для каждого собрания
        foreach ($ids as $i => $id) {
            if (in_array($id, $processed)) {
                continue;
            }

            $idToSet = $id;
            $count = count($heap[$id]['neighbours']);

            //Бежим по соседям, считаем их пересечения, сравниваем
            foreach ($heap[$id]['neighbours'] as $k => $innerId) {
                if (in_array($innerId, $processed)) {
                    continue;
                }

                $innerCount = count($heap[$innerId]['neighbours']);
                if ($innerCount >= $count) {
                    //Больше пересечений - значит менее оптимальное собрание
                    continue;
                }

                $idToSet = $innerId;
                $count = $innerCount;
            }

            //Обработанное собрание и его соседей вносим в список, чтобы не проходить дважды
            $processed[] = $idToSet;
            $processed = array_merge($processed, $heap[$idToSet]['neighbours']);

            $result[] = $heap[$idToSet]['item'];
        }

        usort($result, function ($itemA, $itemB) {
            $dateA = $itemA->date_in;
            $dateB = $itemB->date_in;

            if ($dateA < $dateB) return -1;
            elseif ($dateA > $dateB) return 1;
            else return 0;
        });

        return $result;
    }
}
