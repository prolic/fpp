<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Deriving;
use PHPUnit\Framework\TestCase;

class DerivingTest extends TestCase
{
    /**
     * @test
     */
    public function it_forbids_custom_derivings(): void
    {
        $this->expectException(\LogicException::class);

        new class extends Deriving {
        };
    }
}
