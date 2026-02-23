<?php

namespace core\application\port;

use core\domain\valueObject\JwtToken;
use core\domain\valueObject\QueryParams;

interface IPassportGateway
{
    public function getUserData(JwtToken $token, ?QueryParams $params = null): array;
    public function getContactData(JwtToken $token, ?QueryParams $params = null): array;
    public function getCityData(JwtToken $token, ?QueryParams $params = null): array;
    public function getPostData(JwtToken $token, ?QueryParams $params = null): array;
}