<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\Marker;

use function Fpp\buildDefaultPhpFile;
use function Fpp\char;
use function Fpp\comma;
use Fpp\Configuration;
use Fpp\Definition;
use Fpp\Parser;
use function Fpp\plus;
use function Fpp\sepByList;
use function Fpp\spaces;
use function Fpp\spaces1;
use function Fpp\string;
use Fpp\Type as FppType;
use Fpp\TypeConfiguration;
use function Fpp\typeName;
use Fpp\TypeTrait;

function typeConfiguration(): TypeConfiguration
{
    return new TypeConfiguration(
        parse,
        build,
        null,
        null,
        null,
        null,
        null
    );
}

const parse = 'Fpp\Type\Marker\parse';

function parse(): Parser
{
    return plus(
        for_(
            __($_)->_(spaces()),
            __($_)->_(string('marker')),
            __($_)->_(spaces1()),
            __($m)->_(typeName()),
            __($_)->_(spaces()),
            __($_)->_(char(';'))
        )->call(fn ($m) => new Marker($m, []), $m),
        for_(
            __($_)->_(spaces()),
            __($_)->_(string('marker')),
            __($_)->_(spaces1()),
            __($m)->_(typeName()),
            __($_)->_(spaces()),
            __($_)->_(char(':')),
            __($_)->_(spaces()),
            __($p)->_(sepByList(typeName(), comma())),
            __($_)->_(spaces()),
            __($_)->_(char(';'))
        )->call(fn ($m, $p) => new Marker($m, $p), $m, $p),
    );
}

const markers = 'Fpp\Type\Marker\markers';

function markers(): Parser
{
    return for_(
        __($_)->_(char(':')),
        __($_)->_(spaces()),
        __($ms)->_(sepByList(typeName(), comma())),
    )->yields($ms);
}

const build = 'Fpp\Type\Marker\build';

function build(Definition $definition, array $definitions, Configuration $config): array
{
    $type = $definition->type();

    if (! $type instanceof Marker) {
        throw new \InvalidArgumentException('Can only build definitions of ' . Marker::class);
    }

    $fqcn = $definition->namespace() . '\\' . $type->classname();

    $file = buildDefaultPhpFile($definition, $config);

    $file->addClass($fqcn)
        ->setInterface()
        ->setExtends($type->markers());

    return [$fqcn => $file];
}

class Marker implements FppType
{
    use TypeTrait;
}
