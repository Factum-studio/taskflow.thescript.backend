<?php

declare(strict_types=1);

return [
    // Proxy-маршрут: любой метод на /passport/proxy/{url}
    'passport/proxy/<url:.*>' => 'passport-proxy/proxy/index',
];
