<?php

return [
    'GET task' => 'tasks/task/index',
    'GET task/<id:\d+>' => 'tasks/task/view',
    'POST task' => 'tasks/task/create',
    'PUT task/<id:\d+>' => 'tasks/task/update',
    'DELETE task/<id:\d+>' => 'tasks/task/delete',
    'POST task/<id:\d+>/change-status' => 'tasks/task/change-status',
    'POST task/<id:\d+>/assign' => 'tasks/task/assign',
    'POST task/<id:\d+>/restore' => 'tasks/task/restore',
    'DELETE task/<id:\d+>/hard' => 'tasks/task/hard-delete',

    'GET task-status' => 'tasks/status/index',

    'GET task-priority' => 'tasks/priority/index',

    'GET task/<taskId:\d+>/comment' => 'tasks/comment/index',
    'POST task/<taskId:\d+>/comment' => 'tasks/comment/create',
    'GET comment/<id:\d+>' => 'tasks/comment/view',
    'PUT comment/<id:\d+>' => 'tasks/comment/update',
    'DELETE comment/<id:\d+>' => 'tasks/comment/delete',

    'GET sticker' => 'tasks/sticker/index',
    'POST sticker' => 'tasks/sticker/create',
    'GET sticker/<id:\d+>' => 'tasks/sticker/view',
    'PUT sticker/<id:\d+>' => 'tasks/sticker/update',
    'DELETE sticker/<id:\d+>' => 'tasks/sticker/delete',

    'GET task/<taskId:\d+>/sticker' => 'tasks/task-sticker/index',
    'POST task/<taskId:\d+>/sticker' => 'tasks/task-sticker/attach',
    'DELETE task/<taskId:\d+>/sticker/<stickerId:\d+>' => 'tasks/task-sticker/detach',

    'GET time-interval' => 'tasks/time-interval/index',
    'POST time-interval/start' => 'tasks/time-interval/start',
    'POST time-interval/stop' => 'tasks/time-interval/stop',
    'POST time-interval' => 'tasks/time-interval/create',
    'GET time-interval/daily-summary' => 'tasks/time-interval/daily-summary',
    'GET task/<taskId:\d+>/time-summary' => 'tasks/time-interval/task-summary',
];