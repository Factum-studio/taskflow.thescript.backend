<?php

return [
    'class' => \yii\httpclient\Client::class,
    'baseUrl' => $_ENV['USER_SERVICE_BASE_URL'],
    'requestConfig' => [
        'format' => \yii\httpclient\Client::FORMAT_JSON,
    ],
    'responseConfig' => [
        'format' => \yii\httpclient\Client::FORMAT_JSON,
    ],
];