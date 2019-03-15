<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Definition;

use Fpp\InvalidDeriving;

class Equals extends AbstractDeriving
{
    public const VALUE = 'Equals';

    public function checkDefinition(Definition $definition): void
    {
        foreach ($definition->derivings() as $deriving) {
            if (\in_array((string) $deriving, $this->forbidsDerivings(), true)) {
                throw InvalidDeriving::conflictingDerivings($definition, self::VALUE, (string) $deriving);
            }
        }
    }

    private function forbidsDerivings(): array
    {
        return [
            AggregateChanged::VALUE,
            Command::VALUE,
            DomainEvent::VALUE,
            Enum::VALUE,
            Query::VALUE,
            MicroAggregateChanged::VALUE,
            Uuid::VALUE,
            Exception::VALUE,
        ];
    }
}
