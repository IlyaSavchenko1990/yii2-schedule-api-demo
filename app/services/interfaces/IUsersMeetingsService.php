<?php

namespace app\services\interfaces;

interface IUsersMeetingsService
{

    /**
     * Назначает указанных пользователей на заданное собрание,
     * создавая записи в таблице связей meetings_users_links
     * 
     * @param int $meetingId - Идентификатор собрания
     * @param array $usersIds - Массив идентификаторов пользователей
     */
    public function attachUsersToMeeting(int $meetingId, array $usersIds): int;

    /**
     * Удаляет указанных пользователей с заданного собрания,
     * удаляя их связи в таблице meetings_users_links
     * 
     * @param int $meetingId - Идентификатор собрания
     * @param array $usersIds - Массив идентификаторов пользователей
     */
    public function detachUsersFromMeeting(int $meetingId, array $usersIds): int;

    /**
     * Назначает указанного пользователя на заданные собрания,
     * создавая записи в таблице связей meetings_users_links
     * 
     * @param int $userId - Идентификатор пользователя
     * @param array $meetingsIds - Массив идентификаторов собраний
     */
    public function attachUserToMeetings(int $userId, array $meetingsIds): int;

    /**
     * Удаляет указанного пользователей с заданных собраний,
     * удаляя их связи в таблице meetings_users_links
     * 
     * @param int $userId - Идентификатор пользователя
     * @param array $meetingsIds - Массив идентификаторов собраний
     */
    public function detachUserFromMeetings(int $userId, array $meetingsIds): int;

    /**
     * Находит активные собрания для пользователя за указанную дату,
     * Фильтрует в порядке, обеспечивающим большее кол-во посещений
     * 
     * @param int $userId - Идентификатор пользователя
     * @param string $dateFrom - Дата фильтрации "от" - в формате Y-m-d H:i
     * @param string $dateTo - Дата фильтрации "до" - в формате Y-m-d H:i
     * @return array Отфильтрованный массив собраний
     */
    public function getScheduleForUser(int $userId, string $dateFrom, string $dateTo): array;

    /**
     * Фильтрует массив по алгоритму для посешения максимального кол-ва собраний
     * Сравнивает каждое собрание и определяет кол-во пересечений
     * 
     * @param array Исходный массив собраний
     * @return array Исходный отфильтрованный массив собраний в порядке, обеспечивающим большее кол-во посещений
     */
    public function filterForMaxMeet($meetings): array;
}
