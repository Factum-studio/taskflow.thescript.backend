<?php

use core\application\port\IJwtValidator;
use core\infrastructure\jwt\JwtValidator;

$container = Yii::$container;

$container->set(IJwtValidator::class, function () {
    $config = Yii::$app->params['jwt'];

    return new JwtValidator(
        $config['secret'],
        $config['issuer'],
        $config['audience']
    );
});
