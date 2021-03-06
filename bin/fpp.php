<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

use Nette\PhpGenerator\PsrPrinter;

$path1 = \realpath(__DIR__ . '/../../../../');
$path2 = \realpath(__DIR__ . '/../');
$vendorName = 'vendor';

foreach ([$path1, $path2] as $path) {
    if (\file_exists($composerPath = "$path/composer.json")) {
        $composerJson = \json_decode(\file_get_contents($composerPath), true);
        $vendorName = isset($composerJson['config']['vendor-dir']) ? $composerJson['config']['vendor-dir'] : $vendorName;
    }

    break;
}

if (! \file_exists("$path/$vendorName/autoload.php")) {
    echo "\033[1;31mYou need to set up the project dependencies using the following commands: \033[0m" . PHP_EOL;
    echo 'curl -s http://getcomposer.org/installer | php' . PHP_EOL;
    echo 'php composer.phar install' . PHP_EOL;
    exit(1);
}

$autoloader = require "$path/$vendorName/autoload.php";

if (\file_exists("$path/$vendorName/prolic/fpp/autoload.php")) {
    $fppAutoloader = "$path/$vendorName/prolic/fpp/autoload.php";
} else {
    $fppAutoloader = "$path/autoload.php";
}

require_once $fppAutoloader;

$config = [
    'use_strict_types' => true,
    'source' => '.',
    'target' => '*',
    'success_msg' => 'Successfully generated and written to disk',
    'printer' => fn () => (new PsrPrinter())->setTypeResolving(false),
    'file_parser' => parseFile,
    'comment' => 'Generated by prolic/fpp - do not edit !!!',
    'types' => [
        Type\Command\Command::class => Type\Command\typeConfiguration(),
        Type\Data\Data::class => Type\Data\typeConfiguration(),
        Type\Enum\Enum::class => Type\Enum\typeConfiguration(),
        Type\Event\Event::class => Type\Event\typeConfiguration(),
        Type\String_\String_::class => Type\String_\typeConfiguration(),
        Type\Int_\Int_::class => Type\Int_\typeConfiguration(),
        Type\Float_\Float_::class => Type\Float_\typeConfiguration(),
        Type\Bool_\Bool_::class => Type\Bool_\typeConfiguration(),
        Type\Marker\Marker::class => Type\Marker\typeConfiguration(),
        Type\Uuid\Uuid::class => Type\Uuid\typeConfiguration(),
        Type\Guid\Guid::class => Type\Guid\typeConfiguration(),
        \DateTimeImmutable::class => Type\DateTimeImmutable\typeConfiguration(),
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
    'source' => '.',
    'target' => '*', // * = use composer settings, otherwise give path here
    'success_msg' => 'Successfully generated and written to disk',
    'printer' => fn () => (new PsrPrinter())->setTypeResolving(false),
    'file_parser' => parseFile,
    'comment' => 'Generated by prolic/fpp - do not edit !!!', // put `null` to disable
    'types' => [
        Type\Command\Command::class => Type\Command\\typeConfiguration(),
        Type\Data\Data::class => Type\Data\\typeConfiguration(),
        Type\Enum\Enum::class => Type\Enum\\typeConfiguration(),
        Type\Event\Event::class => Type\Event\\typeConfiguration(),
        Type\String_\String_::class => Type\String_\\typeConfiguration(),
        Type\Int_\Int_::class => Type\Int_\\typeConfiguration(),
        Type\Float_\Float_::class => Type\Float_\\typeConfiguration(),
        Type\Bool_\Bool_::class => Type\Bool_\\typeConfiguration(),
        Type\Marker\Marker::class => Type\Marker\\typeConfiguration(),
        Type\Uuid\Uuid::class => Type\Uuid\\typeConfiguration(),
        Type\Guid\Guid::class => Type\Guid\\typeConfiguration(),
        \DateTimeImmutable::class => Type\DateTimeImmutable\\typeConfiguration(),
    ],
];

CODE;

    \file_put_contents("$path/fpp-config.php", $file);

    echo "Default configuration written to $path/fpp-config.php\n";
    exit(0);
}

if (\file_exists("$path/fpp-config.php")) {
    $config = require "$path/fpp-config.php";
}

$config = Configuration::fromArray($config);

runFpp($config, $autoloader);

echo $config->successMessage() . "\n";
