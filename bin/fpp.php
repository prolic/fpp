<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

use Nette\PhpGenerator\PsrPrinter;
use function Pair;

if (! isset($argv[1])) {
    echo 'Missing input directory or file argument';
    exit(1);
}

$path = $argv[1];

$pwd = \realpath(\getcwd());
$vendorName = 'vendor';

if (\file_exists($composerPath = "{$pwd}/composer.json")) {
    $composerJson = \json_decode(\file_get_contents($composerPath), true);
    $vendorName = isset($composerJson['config']['vendor-dir']) ? $composerJson['config']['vendor-dir'] : $vendorName;
}

if (! \file_exists("{$pwd}/{$vendorName}/autoload.php")) {
    echo "\033[1;31mYou need to set up the project dependencies using the following commands: \033[0m" . PHP_EOL;
    echo 'curl -s http://getcomposer.org/installer | php' . PHP_EOL;
    echo 'php composer.phar install' . PHP_EOL;
    exit(1);
}

require "{$pwd}/{$vendorName}/autoload.php";

$config = [
    'use_strict_types' => true,
    'one_class_per_file' => true, // @todo this configuration isn't working right now
    'printer' => PsrPrinter::class,
    'namespace_parser' => Pair(namespaceName, 'buildNamespace'),
    'types' => [
        Type\Enum::class => Pair(enum, buildEnum),
    ],
];

if ($path === '--gen-config') {
    $file = <<<CODE
<?php

declare(strict_types=1);

namespace Fpp;

use Nette\PhpGenerator\PsrPrinter;
use function Pair;

return [
    'use_strict_types' => true,
    'one_class_per_file' => true, // @todo this configuration isn't working right now
    'printer' => PsrPrinter::class,
    'namespace_parser' => Pair(namespaceName, 'buildNamespace'),
    'types' => [
        Type\Enum::class => Pair(enum, buildEnum),
    ],
];

CODE;

    \file_put_contents("{$pwd}/fpp-config.php", $file);

    echo "Default configuration written to {$pwd}/fpp-config.php\n";
    exit(0);
}

if (\file_exists("{$pwd}/fpp-config.php")) {
    $config = \array_merge_recursive($config, require "{$pwd}/fpp-config.php");
}

if (empty($config['types'])) {
    echo "\033[1;31mNo parser found, check your fpp-config.php file\033[0m" . PHP_EOL;
    exit(1);
}

// bootstrapping done - @todo: make this bottom part more FP stylish
$parser = zero();

foreach ($config['types'] as $type => $pair) {
    $parser = $parser->or(($pair->_1)());
}

$namespaceParser = $config['namespace_parser']->_1;

$toDump = \Nil();

scan($path)->map(
    fn ($f) => Pair(manyList($namespaceParser($parser))->run(\file_get_contents($f)), $f)
)->map(function ($p) use ($config, &$toDump) {
    $parsed = $p->_1->head();
    $filename = $p->_2;

    if (\strlen($parsed->_2) !== 0) {
        echo "\033[1;31mSyntax error at file $filename at:\033[0m" . PHP_EOL . PHP_EOL;
        echo \substr($parsed->_2, 0, 40) . PHP_EOL;
        exit(1);
    }

    $toDump = $toDump->combine($parsed->_1);
});

$toDump->map(function ($ns) use ($config) {
    \var_dump(dump($ns, $config));
});

exit(0);
