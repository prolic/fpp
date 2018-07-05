<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

interface Deriving
{
    const VALUE = '';

    /**
     * @throws InvalidDeriving
     */
    public function checkDefinition(Definition $definition): void;

    public function __toString(): string;

    public function equals(Deriving $deriving): bool;
}
