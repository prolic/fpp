<?php

declare(strict_types=1);

namespace Fpp;

final class ParseError extends \RuntimeException
{
    public static function expectedWhiteSpace(int $line): ParseError
    {
        return new self('Expected T_WHITESPACE at line ' . $line);
    }

    public static function expectedString(int $line): ParseError
    {
        return new self('Expected T_STRING at line ' . $line);
    }

    public static function unexpectedEndOfFile(): ParseError
    {
        return new self('Unexpected end of file');
    }

    public static function unexpectedDeriving(int $line): ParseError
    {
        return new self('Unexpected deriving at line ' . $line);
    }

    public static function expectedNamespaceSeparator(int $line): ParseError
    {
        return new self('Expected T_NS_SEPARATOR at line ' . $line);
    }

    public static function nestedNamespacesDetected(int $line): ParseError
    {
        return new self('Namespace declarations cannot be nested at line ' . $line);
    }

    public static function expectedEquals(int $line): ParseError
    {
        return new self('Expected equals sign at line ' . $line);
    }

    public static function expectedRoundBraces(int $line): ParseError
    {
        return new self('Expected round braces at line ' . $line);
    }

    public static function invalidToken(int $line): ParseError
    {
        return new self('Invalid token at line ' . $line);
    }

    public static function expectedCurlyBraces(int $line): ParseError
    {
        return new self('Expected curly braces at line ' . $line);
    }

    public static function expectedVariable(int $line): ParseError
    {
        return new self('Expected variable at line ' . $line);
    }

    public static function unknownDeriving(int $line): ParseError
    {
        return new self('Unknown deriving at line ' . $line);
    }
}