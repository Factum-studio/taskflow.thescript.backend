<?php

declare(strict_types=1);

/**
 * @example
 * 'module-name'=>[
 *      'class' =>'modules\module-name\Module',
 *      'controllerNamespace' => 'modules\module-name\presentation\controller',
 * ],
 */
return [
    'auth' => [
        'class' => 'modules\passport\auth\Module',
        'controllerNamespace' => 'modules\passport\auth\presentation\controller',
    ],
    'rbac' => [
        'class' => 'modules\\rbac\\Module',
        'controllerNamespace' => 'modules\\rbac\\presentation\\controller',
    ],
    'tasks' => [
        'class' => 'modules\\tasks\\Module',
        'controllerNamespace' => 'modules\\tasks\\presentation\\controller',
    ],
    'projects' => [
        'class' => 'modules\\projects\\Module',
        'controllerNamespace' => 'modules\\projects\\presentation\\controller',
    ],
    'feedback' => [
        'class' => 'modules\\feedback\\Module',
        'controllerNamespace' => 'modules\\feedback\\presentation\\controller',
    ],
];
