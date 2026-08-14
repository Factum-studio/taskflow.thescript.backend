<?php

/**
 * TODO: По мере реализации раскомментировать соответствующий конфиг подключения модулей
 */

return [
//    'clients'=>[
//        'class'=>'modules\clients\Module',
//    ],
    'tasks'=>[
        'class'=>'modules\tasks\Module',
        'controllerNamespace' => 'modules\tasks\presentation\controller',
    ],
    'projects'=>[
        'class'=>'modules\projects\Module',
        'controllerNamespace' => 'modules\projects\presentation\controller',
    ],
//    'kanban'=>[
//        'class'=>'modules\kanban\Module',
//    ],
//    'analytics'=>[
//        'class'=>'modules\analytics\Module',
//    ],
//    'files'=>[
//        'class'=>'modules\files\Module',
//    ],
];