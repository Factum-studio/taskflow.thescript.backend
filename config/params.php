<?php

return [
    'adminEmail' => $_ENV['ADMIN_EMAIL'],
    'senderEmail' => $_ENV['SENDER_EMAIL'],
    'senderName' => $_ENV['SENDER_NAME'],
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'],
        'issuer' => $_ENV['JWT_ISSUER'],
        'audience' => $_ENV['JWT_AUDIENCE'],
        'clockSkew' => 60,
    ],
];
