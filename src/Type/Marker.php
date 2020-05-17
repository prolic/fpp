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

use function Fpp\char;
use function Fpp\comma;
use Fpp\Namespace_;
use Fpp\Parser;
use function Fpp\plus;
use function Fpp\sepByList;
use function Fpp\spaces;
use function Fpp\spaces1;
use function Fpp\string;
use Fpp\Type as FppType;
use function Fpp\typeName;
use Nette\PhpGenerator\ClassType;
use Phunkie\Types\ImmList;
use Phunkie\Types\ImmMap;
use Phunkie\Types\Tuple;

function definition(): Tuple
{
    return \Tuple(parse, build, null, null);
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
        )->call(fn ($m) => new Marker($m, Nil()), $m),
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

function build(Marker $marker, ImmMap $builders): ClassType
{
    $class = new ClassType($marker->classname());
    $class->setInterface();

    $marker->parentMarkers()->map(
        function ($i) use ($class) {
            $class->setExtends($i);
        }
    );

    return $class;
}

class Marker implements FppType
{
    private ?Namespace_ $namespace = null;
    private string $classname;
    /** @var Immlist<string> */
    private ImmList $parentMarkers;

    public function __construct(string $classname, ImmList $parentMarkers)
    {
        $this->classname = $classname;
        $this->parentMarkers = $parentMarkers;
    }

    public function classname(): string
    {
        return $this->classname;
    }

    /**
     * @return ImmList<string>
     */
    public function parentMarkers(): ImmList
    {
        return $this->parentMarkers;
    }

    public function namespace(): Namespace_
    {
        return $this->namespace;
    }

    public function setNamespace(Namespace_ $namespace): void
    {
        $this->namespace = $namespace;
    }
}
