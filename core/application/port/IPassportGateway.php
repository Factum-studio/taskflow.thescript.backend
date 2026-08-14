<?php

namespace core\application\port;

use core\domain\valueObject\JwtToken;
use core\domain\valueObject\QueryParams;

interface IPassportGateway
{
    /**
     * Получить список пользователей (с пагинацией)
     * @param JwtToken $token Системный токен
     * @param QueryParams|null $params Параметры пагинации/фильтрации
     * @return array Массив с ключами 'items' и 'total'
     */
    public function getAllUsers(JwtToken $token, ?QueryParams $params = null): array;
    public function getUserData(JwtToken $token, ?QueryParams $params = null): array;
    public function getUserById(string $id, JwtToken $token, ?QueryParams $params = null): array;
    public function getContactData(JwtToken $token, ?QueryParams $params = null): array;
    public function getCityData(JwtToken $token, ?QueryParams $params = null): array;
    public function getPostData(JwtToken $token, ?QueryParams $params = null): array;
    public function findUserByEmail(string $email, JwtToken $token): ?array;
}