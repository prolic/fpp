<?php

declare(strict_types=1);

namespace Fpp;

final class ParseError extends \RuntimeException
{
    public static function unexpectedTokenFound(string $expected, array $actual): ParseError
    {
        return new self("Syntax error, unexpected '$actual[1]', expecting '$expected' at line $actual[2]");
    }

    public static function expectedString(array $actual): ParseError
    {
        return new self("Syntax error, unexpected '$actual[1]', expecting identifier (T_STRING) at line $actual[2]");
    }

    public static function unexpectedEndOfFile(): ParseError
    {
        return new self('Unexpected end of file');
    }

    public static function nestedNamespacesDetected(int $line): ParseError
    {
        return new self('Namespace declarations cannot be nested at line ' . $line);
    }

    public static function unknownDeriving(int $line): ParseError
    {
        return new self('Unknown deriving at line ' . $line);
    }
}