<?php

declare(strict_types=1);

namespace Fpp;

require __DIR__ . '/../vendor/autoload.php';

$files = [
    'dump.php',
    'helpers.php',
    'loadTemplate.php',
    'parse.php',
    'replace.php',
    'scan.php',
    'builder/buildAccessors.php',
    'builder/buildClassExtends.php',
    'builder/buildClassName.php',
    'builder/buildEqualsBody.php',
    'builder/buildFromArrayBody.php',
    'builder/buildMessageName.php',
    'builder/buildProperties.php',
    'builder/buildSetters.php',
    'builder/buildVariableName.php',
];

foreach ($files as $file) {
    require_once __DIR__ . '/' . $file;
}
