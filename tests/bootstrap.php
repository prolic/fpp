<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

require_once \dirname(__DIR__).'/src/bootstrap.php';

// This could probably be handled by some clever autoloading
// but for now, this makes sure the files are created before loading the tests.
$output = null;
\exec(
    'php ' . \dirname(__DIR__) . '/bin/fpp ' . __DIR__ . '/Fixtures/to_array.fpp',
    $output,
    $exitCode
);
if ($exitCode !== 0) {
    echo \implode("\n", $output);
    exit($exitCode);
}
