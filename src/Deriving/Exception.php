<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Deriving;

use Fpp\Definition;

class Exception extends AbstractDeriving
{
    public const VALUE = 'Exception';

    private $parentClass;

    public function __construct(string $parentClass = '\Exception')
    {
        $this->parentClass = $parentClass;
    }

    public function parentClass(): string
    {
        return $this->parentClass;
    }

    public function checkDefinition(Definition $definition): void
    {
    }

    private function forbidsDerivings(): array
    {
        return [
            AggregateChanged::VALUE,
            Command::VALUE,
            DomainEvent::VALUE,
            Query::VALUE,
            MicroAggregateChanged::VALUE,
            ToArray::VALUE,
            ToScalar::VALUE,
            ToString::VALUE,
            Uuid::VALUE,
        ];
    }
}
