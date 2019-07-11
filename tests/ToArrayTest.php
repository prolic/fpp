<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest\Functional;

use FppTest\Fixtures\Generated\Email;
use FppTest\Fixtures\Generated\PhoneNumber;
use FppTest\Fixtures\Generated\User;
use FppTest\Fixtures\Generated\UserId;
use PHPUnit\Framework\TestCase;

class ToArrayTest extends TestCase
{
    /**
     * @dataProvider get_test_cases
     */
    public function test_to_array_returns_an_array(User $user, array $array): void
    {
        self::assertEquals($array, $user->toArray());
    }

    public function get_test_cases(): array
    {
        return [
            'with_phone_number' => [
                new User(
                    UserId::fromString('7284e062-7a8b-4b17-b4fa-8c162143475d'),
                    'foo',
                    Email::fromString('foo@example.com'),
                    [],
                    ['bar'],
                    [PhoneNumber::fromString('+331234567890')]
                ),
                [
                    'id' => '7284e062-7a8b-4b17-b4fa-8c162143475d',
                    'name' => 'foo',
                    'email' => 'foo@example.com',
                    'secondaryEmails' => [],
                    'nickNames' => ['bar'],
                    'phoneNumbers' => ['+331234567890'],
                ],
            ],
            'with_null_object_array' => [
                new User(
                    UserId::fromString('7284e062-7a8b-4b17-b4fa-8c162143475d'),
                    'foo',
                    Email::fromString('foo@example.com'),
                    [],
                    ['bar'],
                    null
                ),
                [
                    'id' => '7284e062-7a8b-4b17-b4fa-8c162143475d',
                    'name' => 'foo',
                    'email' => 'foo@example.com',
                    'secondaryEmails' => [],
                    'nickNames' => ['bar'],
                    'phoneNumbers' => null,
                ],
            ],
        ];
    }
}
