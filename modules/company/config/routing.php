<?php

return [
    // Компании
    'GET company' => 'company/company/index',
    'GET company/<id:\d+>' => 'company/company/view',
    'POST company' => 'company/company/create',
    'PUT company/<id:\d+>' => 'company/company/update',
    'DELETE company/<id:\d+>' => 'company/company/delete',
    'POST company/<id:\d+>/archive' => 'company/company/archive',
    'POST company/<id:\d+>/unarchive' => 'company/company/unarchive',
    'POST company/<id:\d+>/verify' => 'company/company/verify',

    // Участники компании
    'GET company/<companyId:\d+>/member' => 'company/company-member/index',
    'PUT company/<companyId:\d+>/member/<userId:\d+>/role' => 'company/company-member/change-role',
    'DELETE company/<companyId:\d+>/member/<userId:\d+>' => 'company/company-member/delete',
    'POST company/<companyId:\d+>/transfer/<userId:\d+>' => 'company/company-member/transfer',

    // Приглашения в компанию
    'POST company/<id:\d+>/invite' => 'company/company-invitation/invite',
    'POST company/invitation/accept' => 'company/company-invitation/accept',
    'DELETE company/<companyId:\d+>/invitation/<invitationId:\d+>' => 'company/company-invitation/cancel',
];