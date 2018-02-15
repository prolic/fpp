<?php

declare(strict_types=1);

namespace Fpp;

final class ParseError extends \RuntimeException
{
    public static function unknownDefinition(array $actual, string $filename): ParseError
    {
        $filePart = empty($filename) ? '' : ' on file \'' . $filename . '\'';

        return new self(sprintf(
            "Syntax error, unexpected '%s', expecting 'data' at line %d%s",
            $actual[1],
            $actual[2],
            $filePart
        ));
    }

    public static function unexpectedTokenFound(string $expected, array $actual, string $filename): ParseError
    {
        $filePart = empty($filename) ? '' : ' on file \'' . $filename . '\'';

        return new self(sprintf(
            "Syntax error, unexpected '%s', expecting '%s' at line %d%s",
            $actual[1],
            $expected,
            $actual[2],
            $filePart
        ));
    }

    public static function expectedString(array $actual, string $filename): ParseError
    {
        $filePart = empty($filename) ? '' : ' on file \'' . $filename . '\'';

        return new self(sprintf(
            "Syntax error, unexpected '%s', expecting identifier (T_STRING) at line %d%s",
            $actual[1],
            $actual[2],
            $filePart
        ));
    }

    public static function unexpectedEndOfFile(string $filename): ParseError
    {
        $filePart = empty($filename) ? '' : ' on file \'' . $filename . '\'';

        return new self('Unexpected end of file' . $filePart);
    }

    public static function nestedNamespacesDetected(int $line, string $filename): ParseError
    {
        $filePart = empty($filename) ? '' : ' on file \'' . $filename . '\'';

        return new self('Namespace declarations cannot be nested at line ' . $line . $filePart);
    }

    public static function unknownDeriving(int $line, string $filename): ParseError
    {
        $filePart = empty($filename) ? '' : ' on file \'' . $filename . '\'';

        return new self('Unknown deriving at line ' . $line . $filePart);
    }
}
