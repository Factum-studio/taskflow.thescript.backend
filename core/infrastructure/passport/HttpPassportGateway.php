<?php

namespace core\infrastructure\passport;

use core\application\port\IPassportGateway;
use core\application\port\IUrlBuilder;
use core\domain\valueObject\JwtToken;
use core\domain\valueObject\QueryParams;
use core\infrastructure\http\config\ApiEndpointConfig;
use yii\httpclient\Client;

final class HttpPassportGateway implements IPassportGateway
{
    private Client $httpClient;
    private IUrlBuilder $urlBuilder;
    private ApiEndpointConfig $endpointConfig;

    public function __construct(
        Client $httpClient,
        IUrlBuilder $urlBuilder,
        ApiEndpointConfig $endpointConfig
    ) {
        $this->httpClient = $httpClient;
        $this->urlBuilder = $urlBuilder;
        $this->endpointConfig = $endpointConfig;
    }

    public function getUserData(JwtToken $token, ?QueryParams $params = null): array
    {
        return $this->sendRequest('user', $token, $params);
    }

    public function getContactData(JwtToken $token, ?QueryParams $params = null): array
    {
        return $this->sendRequest('contact', $token, $params);
    }

    public function getCityData(JwtToken $token, ?QueryParams $params = null): array
    {
        return $this->sendRequest('city', $token, $params);
    }

    public function getPostData(JwtToken $token, ?QueryParams $params = null): array
    {
        return $this->sendRequest('post', $token, $params);
    }

    private function sendRequest(string $endpointKey, JwtToken $token, ?QueryParams $params = null): array
    {
        $url = $this->urlBuilder->build(
            $this->endpointConfig->get($endpointKey),
            $params
        );

        $response = $this->httpClient->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->setHeaders([
                'Authorization' => 'Bearer ' . $token->value(),
            ])
            ->send();

        if (!$response->isOk) {
            throw new \RuntimeException(
                'Passport request failed: ' . $response->statusCode . ' - ' . $response->content
            );
        }

        return $response->data;
    }
}