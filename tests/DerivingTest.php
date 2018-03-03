<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest;

use PHPUnit\Framework\TestCase;
use function Fpp\defaultDerivingMap;

class DerivingTest extends TestCase
{
    /**
     * @test
     */
    public function it_delivers_forbidden_derivings_and_to_string(): void
    {
        $derivingMap = defaultDerivingMap();

        foreach ($derivingMap as $name => $deriving) {
            $this->assertSame($name, (string) $deriving);
        }
    }
}
