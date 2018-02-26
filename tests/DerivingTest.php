<?php

declare(strict_types=1);

namespace FppTest;

use function Fpp\defaultDerivingMap;
use PHPUnit\Framework\TestCase;

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
