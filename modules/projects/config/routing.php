<?php

return [
    // Проекты
    'GET project' => 'projects/project/index',
    'GET project/<id:\d+>' => 'projects/project/view',
    'POST project' => 'projects/project/create',
    'PUT project/<id:\d+>' => 'projects/project/update',
    'DELETE project/<id:\d+>' => 'projects/project/delete',

    // Доски
    'GET project/<projectId:\d+>/board' => 'projects/board/index',
    'GET board/<id:\d+>' => 'projects/board/view',
    'POST project/<projectId:\d+>/board' => 'projects/board/create',
    'PUT board/<id:\d+>' => 'projects/board/update',
    'DELETE board/<id:\d+>' => 'projects/board/delete',

    // Участники проекта
    'GET project/<projectId:\d+>/member' => 'projects/project-member/index',
    'POST project/<projectId:\d+>/member' => 'projects/project-member/create',
    'PUT project/<projectId:\d+>/member/<userId:\d+>/role' => 'projects/project-member/update-role',
    'DELETE project/<projectId:\d+>/member/<userId:\d+>' => 'projects/project-member/delete',

    // Приглашения
    'POST project/<id:\d+>/invite' => 'projects/invitation/invite',
    'POST invitation/accept' => 'projects/invitation/accept',
    'DELETE project/<id:\d+>/invitation/<email:.+>' => 'projects/invitation/cancel',
];