<?php

declare(strict_types=1);

/**
 * @example
 * '<source>\module-name\infrastructure\migrations'
 */
return [
    'core\\infrastructure\\migrations',
    'modules\\rbac\\infrastructure\\migrations',
    'modules\\tasks\\infrastructure\\migrations',       // TODO: special control this module
    'modules\\projects\\infrastructure\\migrations',    // TODO: special control this module
    'modules\\feedback\\infrastructure\\migrations',    // TODO: special control this module
];
