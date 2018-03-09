<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;

const buildImports = '\Fpp\Builder\buildImports';

function buildImports(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\Uuid())) {
            return "use Ramsey\Uuid\Uuid;\n    use Ramsey\Uuid\UuidInterface;\n";
        }

        if ($deriving->equals(new Deriving\AggregateChanged())) {
            return "use Prooph\Common\Messaging\DomainEvent;\n";
        }

        if ($deriving->equals(new Deriving\DomainEvent())) {
            return "use Prooph\Common\Messaging\DomainEvent;\n    use Prooph\Common\Messaging\PayloadTrait;\n";
        }

        if ($deriving->equals(new Deriving\Command())) {
            return "use Prooph\Common\Messaging\Command;\n    use Prooph\Common\Messaging\PayloadTrait;\n";
        }

        if ($deriving->equals(new Deriving\Query())) {
            return "use Prooph\Common\Messaging\PayloadTrait;\n    use Prooph\Common\Messaging\Query;\n";
        }
    }

    return $placeHolder;
}
