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

class InvalidDeriving extends \RuntimeException
{
    public static function conflictingDerivings(
        Definition $definition,
        string $deriving1,
        string $deriving2
    ): InvalidDeriving {
        return new self(\sprintf(
            'Invalid deriving on %s, deriving %s conflicts with deriving %s',
            self::className($definition),
            $deriving1,
            $deriving2
        ));
    }

    public static function exactlyOneConstructorExpected(Definition $definition, string $deriving): InvalidDeriving
    {
        return new self(\sprintf(
            'Invalid deriving on %s, deriving %s expects exactly one constructor',
            self::className($definition),
            $deriving
        ));
    }

    public static function atLeastTwoConstructorsExpected(Definition $definition, string $deriving): InvalidDeriving
    {
        return new self(\sprintf(
            'Invalid deriving on %s, deriving %s expects at least two constructors',
            self::className($definition),
            $deriving
        ));
    }

    public static function exactlyZeroConstructorArgumentsExpected(Definition $definition, string $deriving): InvalidDeriving
    {
        return new self(\sprintf(
            'Invalid deriving on %s, deriving %s expects exactly zero constructor arguments',
            self::className($definition),
            $deriving
        ));
    }

    public static function noConstructorNamespacesAllowed(Definition $definition, string $deriving): InvalidDeriving
    {
        return new self(\sprintf(
            'Invalid deriving on %s, deriving %s expects constructor without any namespace',
            self::className($definition),
            $deriving
        ));
    }

    public static function exactlyOneConstructorArgumentExpected(Definition $definition, string $deriving): InvalidDeriving
    {
        return new self(\sprintf(
            'Invalid deriving on %s, deriving %s expects exactly one constructor argument',
            self::className($definition),
            $deriving
        ));
    }

    public static function atLeastOneConstructorArgumentExpected(Definition $definition, string $deriving): InvalidDeriving
    {
        return new self(\sprintf(
            'Invalid deriving on %s, deriving %s expects at least one constructor argument',
            self::className($definition),
            $deriving
        ));
    }

    public static function noConditionsExpected(Definition $definition, string $deriving): InvalidDeriving
    {
        return new self(\sprintf(
            'Invalid deriving on %s, deriving %s expects no conditions at all',
            self::className($definition),
            $deriving
        ));
    }

    public static function enumValueMappingDoesNotMatchConstructors(Definition $definition): InvalidDeriving
    {
        return new self(\sprintf(
            'Invalid deriving on %s, enum value mapping does not match constructors',
            self::className($definition)
        ));
    }

    public static function invalidFirstArgumentForDeriving(Definition $definition, string $deriving): InvalidDeriving
    {
        return new self(\sprintf(
            'Invalid first argument for %s, %s deriving needs first argument to be no nullable and no list',
            self::className($definition),
            $deriving
        ));
    }

    public static function noArgumentsExpected(string $deriving): InvalidDeriving
    {
        return new self(\sprintf(
            "Deriving %s doesn't expect any arguments",
            $deriving
        ));
    }

    private static function className(Definition $definition): string
    {
        return $definition->namespace() . '\\' . $definition->name();
    }
}
