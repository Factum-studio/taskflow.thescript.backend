<?php

namespace core\infrastructure\passport;

use core\application\dto\CollectionDto;
use core\application\port\IPassportGateway;
use core\domain\valueObject\JwtToken;
use yii\httpclient\Client;

final class HttpPassportGateway implements IPassportGateway
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getUserData(JwtToken $token): array
    {
        $response = $this->httpClient->createRequest()
            ->setMethod('GET')
            ->setUrl('/v1/user')
            ->setHeaders([
                'Authorization' => 'Bearer ' . $token->value(),
            ])
            ->send();

        if (!$response->isOk) {
            throw new \RuntimeException(
                'Passport request failed: ' . $response->statusCode
            );
        }

        return $response->data;
    }
}