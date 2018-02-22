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
    public function it_delivers_forbidden_derivings_and_to_string(): void
    {
        $derivingsMap = [
            'AggregateChanged' => new Deriving\AggregateChanged(),
            'Command' => new Deriving\Command(),
            'DomainEvent' => new Deriving\DomainEvent(),
            'Enum' => new Deriving\Enum(),
            'Equals' => new Deriving\Equals(),
            'FromArray' => new Deriving\FromArray(),
            'FromScalar' => new Deriving\FromScalar(),
            'FromString' => new Deriving\FromString(),
            'Query' => new Deriving\Query(),
            'ToArray' => new Deriving\ToArray(),
            'ToScalar' => new Deriving\ToScalar(),
            'ToString' => new Deriving\ToString(),
            'Uuid' => new Deriving\Uuid(),
        ];

        foreach ($derivingsMap as $name => $deriving) {
            $this->assertSame($name, (string) $deriving);
        }
    }
}
