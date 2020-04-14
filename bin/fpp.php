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

use Fpp\Type\EnumType;
use Fpp\Type\NamespaceType;
use Fpp\Type\Type;
use Nette\PhpGenerator\PsrPrinter;
use function Pair;
use Phunkie\Types\ImmList;
use Phunkie\Types\Pair;

if (! isset($argv[1])) {
    echo 'Missing input directory or file argument';
    exit(1);
}

$path = $argv[1];

$pwd = \realpath(\getcwd());
$vendorName = 'vendor';

if (\file_exists($composerPath = "$pwd/composer.json")) {
    $composerJson = \json_decode(\file_get_contents($composerPath), true);
    $vendorName = isset($composerJson['config']['vendor-dir']) ? $composerJson['config']['vendor-dir'] : $vendorName;
}

if (! \file_exists("$pwd/$vendorName/autoload.php")) {
    echo "\033[1;31mYou need to set up the project dependencies using the following commands: \033[0m" . PHP_EOL;
    echo 'curl -s http://getcomposer.org/installer | php' . PHP_EOL;
    echo 'php composer.phar install' . PHP_EOL;
    exit(1);
}

$autoloader = require "$pwd/$vendorName/autoload.php";

$prefixesPsr4 = $autoloader->getPrefixesPsr4();
$prefixesPsr0 = $autoloader->getPrefixes();

$locatePsrPath = function (string $classname) use ($prefixesPsr4, $prefixesPsr0): string {
    return locatePsrPath($prefixesPsr4, $prefixesPsr0, $classname);
};

$config = [
    'use_strict_types' => true,
    'printer' => PsrPrinter::class,
    'types' => [
        EnumType::class => Pair(enum, buildEnum),
    ],
];

if ($path === '--gen-config') {
    $file = <<<CODE
<?php

declare(strict_types=1);

namespace Fpp;

use Nette\PhpGenerator\PsrPrinter;

return [
    'use_strict_types' => true,
    'printer' => PsrPrinter::class,
    'types' => [
        // key value pair with
        // key = class name of a type
        // value = Pair(parser, builder)
        // f.e.
        // Type\EnumType::class => \Pair(enum, buildEnum),
    ],
];

CODE;

    \file_put_contents("$pwd/fpp-config.php", $file);

    echo "Default configuration written to $pwd/fpp-config.php\n";
    exit(0);
}

if (\file_exists("$pwd/fpp-config.php")) {
    $config = mergeCustomConfig($config, require "$pwd/fpp-config.php");
}

$parser = zero();
$printer = new $config['printer']();

foreach ($config['types'] as $type => $pair) {
    $parser = $parser->or(($pair->_1)());
}

scan($path)->map(
    fn ($f) => Pair(manyList(multipleNamespaces($parser))->run(\file_get_contents($f)), $f)
)->map(function (Pair $p) {
    $parsed = $p->_1;
    $filename = $p->_2;

    $p = $parsed->head();

    if ($p->_2 !== '') {
        echo "\033[1;31mSyntax error at file $filename at:\033[0m" . PHP_EOL . PHP_EOL;
        echo \substr($p->_2, 0, 100) . PHP_EOL;
        exit(1);
    }

    return $p->_1;
})->fold(Nil(), function (ImmList $types, ImmList $nsl) {
    $nsl->map(function (NamespaceType $n) use (&$types) {
        $n->types()->map(function (Type $t) use ($n, &$types) {
            $types = $types->combine(\ImmList(Pair($t, $n)));
        });
    });

    return $types;
})->map(function (Pair $p) use ($printer, $config) {
    $type = $p->_1;
    $namespace = $p->_2;

    return Pair(dump($printer, $type, $namespace, $config), $namespace->name() . '\\' . $type->classname());
})->map(function (Pair $p) use ($locatePsrPath) {
    $filename = $locatePsrPath($p->_2);
    $directory = \dirname($filename);

    if (! \is_dir($directory)) {
        \mkdir($directory, 0777, true);
    }

    \file_put_contents($filename, $p->_1);
});

echo "Successfully generated and written to disk\n";
exit(0);
