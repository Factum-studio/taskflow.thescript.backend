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
];