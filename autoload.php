<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

$autoloadFiles = [
    __DIR__ . '/src/Configuration.php',
    __DIR__ . '/src/Definition.php',
    __DIR__ . '/src/Namespace_.php',
    __DIR__ . '/src/Parser.php',
    __DIR__ . '/src/Type.php',
    __DIR__ . '/src/TypeTrait.php',
    __DIR__ . '/src/Functions/basic_parser.php',
    __DIR__ . '/src/Functions/fpp_parser.php',
    __DIR__ . '/src/Functions/fpp.php',
    __DIR__ . '/src/Functions/scan.php',
    __DIR__ . '/src/Type/Bool_.php',
    __DIR__ . '/src/Type/Data.php',
    __DIR__ . '/src/Type/Enum.php',
    __DIR__ . '/src/Type/Float_.php',
    __DIR__ . '/src/Type/Int_.php',
    __DIR__ . '/src/Type/Marker.php',
    __DIR__ . '/src/Type/String_.php',
    __DIR__ . '/src/Type/Uuid.php',
    __DIR__ . '/src/Type/Guid.php',
];

foreach ($autoloadFiles as $f) {
    require_once $f;
}
