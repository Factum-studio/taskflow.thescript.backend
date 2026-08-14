<?php

return [
    'class'     => 'yii\redis\Connection',
    'hostname'  => $_ENV['REDIS_HOST'],
    'port'      => $_ENV['REDIS_PORT'],
    'password'  => $_ENV['REDIS_PASSWORD'],
    'database'  => 0,
];