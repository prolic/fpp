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

if (! \defined('T_OTHER')) {
    \define('T_OTHER', 100000);
}

const parse = '\Fpp\parse';

function parse(string $filename, array $derivingMap): DefinitionCollection
{
    if (! \is_file($filename)) {
        throw new \RuntimeException("'$filename' is not a file");
    }

    if (! \is_readable($filename)) {
        throw new \RuntimeException("'$filename' is not readable");
    }

    $definitionType = null;
    $namespaceFound = false;
    $contents = \file_get_contents($filename);
    $tokens = \token_get_all("<?php\n\n$contents");

    $collection = new DefinitionCollection();

    $tokenCount = \count($tokens);
    $position = 0;
    $line = 1;
    $namespace = '';

    $nextToken = function () use ($tokens, &$position, &$tokenCount, &$line, $filename): array {
        nextToken:

        if ($position === $tokenCount - 1) {
            throw ParseError::unexpectedEndOfFile($filename);
        }

        $token = $tokens[++$position];

        if (! \is_array($token)) {
            $token = [
                T_OTHER,
                $token,
                $line,
            ];
        } else {
            $token[2] -= 2;
            $line = $token[2];
        }

        if ($token[0] === T_COMMENT) {
            if ($position === $tokenCount - 1) {
                ++$position;

                return $token;
            }
            goto nextToken;
        }

        return $token;
    };

    $skipWhitespace = function (array $token) use ($nextToken): array {
        while ($token[0] === T_WHITESPACE) {
            $token = $nextToken();
        }

        return $token;
    };

    $requireWhitespace = function (array $token) use ($filename): void {
        if ($token[0] !== T_WHITESPACE) {
            throw ParseError::unexpectedTokenFound(' ', $token, $filename);
        }
    };

    $requireString = function (array $token) use ($filename): void {
        if ($token[0] !== T_STRING) {
            throw ParseError::unexpectedTokenFound('T_STRING', $token, $filename);
        }
    };

    $requireVariable = function (array $token) use ($filename): void {
        if ($token[0] !== T_VARIABLE) {
            throw ParseError::unexpectedTokenFound('T_VARIABLE', $token, $filename);
        }
    };

    $isEndOfFile = function () use (&$position, &$tokenCount): bool {
        return $position === ($tokenCount - 1);
    };

    $nextToken();

    if ($isEndOfFile()) {
        return $collection;
    }

    $token = $nextToken();

    while ($position < $tokenCount) {
        switch ($token[0]) {
            case T_NAMESPACE:
                if ($namespaceFound) {
                    throw ParseError::nestedNamespacesDetected($token[2], $filename);
                }

                $token = $nextToken();
                $requireWhitespace($token);
                $token = $nextToken();
                $requireString($token);
                $namespace = $token[1];
                $token = $nextToken();

                while ($token[0] === T_NS_SEPARATOR) {
                    $token = $nextToken();
                    $requireString($token);
                    $namespace .= '\\' . $token[1];
                    $token = $nextToken();
                }

                $token = $skipWhitespace($token);

                if ($token[1] === '{') {
                    $namespaceFound = true;
                    break;
                }

                if ($token[1] !== ';') {
                    throw ParseError::unexpectedTokenFound(';', $token, $filename);
                }
                break;
            case T_STRING:
                switch ($token[1]) {
                    case 'data':
                        $definitionType = DefinitionType::data();
                        goto parseDataDefinition;

                    case 'marker':
                        $definitionType = DefinitionType::marker();
                        goto parseMarkerDefinition;

                    default:
                        throw ParseError::unknownDefinition($token, $filename);
                }

                parseMarkerDefinition:
                $token = $nextToken();
                $requireWhitespace($token);
                $token = $nextToken();
                $requireString($token);
                $name = $token[1];
                $token = $skipWhitespace($nextToken());
                $constructors = [];
                $derivings = [];
                $conditions = [];
                $messageName = null;
                $markers = [];
                if (':' === $token[1]) {
                    do {
                        $markerName = '';
                        $token = $skipWhitespace($nextToken());

                        if ($token[0] === T_NS_SEPARATOR) {
                            $markerName = '\\';
                            $token = $nextToken();
                        }

                        $requireString($token);
                        $markerName .= $token[1];
                        $token = $nextToken();

                        while ($token[0] === T_NS_SEPARATOR) {
                            $token = $nextToken();
                            $requireString($token);
                            $markerName .= '\\' . $token[1];
                            $token = $nextToken();
                        }

                        $markers[] = new MarkerReference($markerName);
                        $token = $skipWhitespace($token);
                    } while (',' === $token[1]);
                }
                if (';' !== $token[1]) {
                    throw ParseError::unexpectedTokenFound(';', $token, $filename);
                }
                goto buildDefinition;

                parseDataDefinition:
                // parse name (incl. message name for prooph messages)
                $token = $nextToken();
                $requireWhitespace($token);
                $token = $nextToken();
                $requireString($token);
                $name = $token[1];
                $token = $skipWhitespace($nextToken());
                $messageName = null;
                $markers = [];

                if (':' === $token[1]) {
                    do {
                        $markerName = '';
                        $token = $skipWhitespace($nextToken());

                        if ($token[0] === T_NS_SEPARATOR) {
                            $markerName = '\\';
                            $token = $nextToken();
                        }

                        $requireString($token);
                        $markerName .= $token[1];
                        $token = $nextToken();

                        while ($token[0] === T_NS_SEPARATOR) {
                            $token = $nextToken();
                            $requireString($token);
                            $markerName .= '\\' . $token[1];
                            $token = $nextToken();
                        }

                        $markers[] = new MarkerReference($markerName);
                        $token = $skipWhitespace($token);
                    } while (',' === $token[1]);
                }

                if ($token[1] !== '=') {
                    throw ParseError::unexpectedTokenFound('=', $token, $filename);
                }

                // parse constructors
                $constructors = [];
                $derivings = [];
                $conditions = [];
                parseConstructor:

                $constructorName = '';
                $arguments = [];
                $token = $skipWhitespace($nextToken());

                if ($token[0] === T_NS_SEPARATOR) {
                    $constructorName = '\\';
                    $token = $nextToken();
                }

                $requireString($token);
                $constructorName .= $token[1];
                $token = $nextToken();

                while ($token[0] === T_NS_SEPARATOR) {
                    $constructorName .= $token[1];
                    $token = $nextToken();
                    $requireString($token);
                    $constructorName .= $token[1];
                    $token = $nextToken();
                }

                if (\in_array($constructorName, ['Bool', 'Float', 'Int', 'String'], true)
                    && $token[1] === '['
                ) {
                    $token = $nextToken();

                    if ($token[1] !== ']') {
                        throw ParseError::unexpectedTokenFound(']', $token, $filename);
                    }

                    $token = $nextToken();
                    $constructorName .= '[]';
                }

                if ($namespace
                    && \substr($constructorName, 0, 1) !== '\\'
                    && ! \in_array($constructorName, ['Bool', 'Bool[]', 'Float', 'Float[]', 'Int', 'Int[]', 'String', 'String[]'], true)
                ) {
                    $constructorName = $namespace . '\\' . $constructorName;
                } elseif (\substr($constructorName, 0, 1) === '\\') {
                    $constructorName = \substr($constructorName, 1);
                }

                $token = $skipWhitespace($token);

                if ($token[1] === '{') {
                    $arguments = [];
                    parseArguments:

                    while ($token[1] !== '}') {
                        $token = $nextToken();
                        $type = null;
                        $nullable = false;

                        $token = $skipWhitespace($token);

                        if ($token[1] === '?') {
                            $nullable = true;
                            $token = $skipWhitespace($nextToken());
                            if ($token[0] !== T_STRING && $token[0] !== T_NS_SEPARATOR) {
                                throw ParseError::unexpectedTokenFound('T_STRING or T_NS_SEPARATOR', $token, $filename);
                            }
                        }

                        if ($token[0] === T_NS_SEPARATOR) {
                            $type = '\\';
                            $token = $nextToken();
                        }

                        if ($token[0] === T_STRING) {
                            $type .= $token[1];

                            if (! \in_array($type, ['string', 'int', 'bool', 'float'], true)) {
                                $requireString($token);
                            }

                            $token = $nextToken();

                            $isList = false;

                            if ($token[1] === '[') {
                                $token = $nextToken();

                                if ($token[1] !== ']') {
                                    throw ParseError::unexpectedTokenFound(']', $token, $filename);
                                }
                                $token = $nextToken();
                                $requireWhitespace($token);
                                $isList = true;
                            }

                            while ($token[0] !== T_WHITESPACE) {
                                if ($token[0] !== T_NS_SEPARATOR) {
                                    throw ParseError::unexpectedTokenFound('T_WHITESPACE or T_NS_SEPARATOR', $token, $filename);
                                }

                                $type .= '\\';
                                $token = $nextToken();
                                $requireString($token);
                                $type .= $token[1];
                                $token = $nextToken();

                                if ($token[1] === '[') {
                                    $token = $nextToken();

                                    if ($token[1] !== ']') {
                                        throw ParseError::unexpectedTokenFound(']', $token, $filename);
                                    }

                                    $token = $nextToken();
                                    $requireWhitespace($token);
                                    $isList = true;
                                }
                            }

                            if (\substr($type, 0, 1) === '\\') {
                                $type = \substr($type, 1);
                            } elseif (\substr($type, 0, 1) !== '\\'
                                && ! \in_array($type, ['string', 'int', 'bool', 'float'], true)
                            ) {
                                $type = $namespace . '\\' . $type;
                            }

                            $token = $nextToken();
                            $requireVariable($token);
                            $argumentName = \substr($token[1], 1);
                            $token = $skipWhitespace($nextToken());

                            if (\in_array($token[1], [',', '}'], true)) {
                                $arguments[] = new Argument($argumentName, $type, $nullable, $isList);
                                goto parseArguments;
                            }
                            throw ParseError::unexpectedTokenFound(', or }', $token, $filename);
                        } elseif ($token[0] === T_VARIABLE) {
                            $arguments[] = new Argument(\substr($token[1], 1));
                            $token = $skipWhitespace($nextToken());

                            if (\in_array($token[1], [',', '}'], true)) {
                                goto parseArguments;
                            }
                            throw ParseError::unexpectedTokenFound(', or }', $token, $filename);
                        }
                    }

                    $token = $nextToken();
                }

                if ('|' === $token[1]) {
                    $constructors[] = new Constructor($constructorName, $arguments);
                    goto parseConstructor;
                }

                if (';' === $token[1]) {
                    $constructors[] = new Constructor($constructorName, $arguments);
                    goto buildDefinition;
                }

                $token = $skipWhitespace($token);
                $constructors[] = new Constructor($constructorName, $arguments);

                if ('|' === $token[1]) {
                    goto parseConstructor;
                }

                if (';' === $token[1]) {
                    goto buildDefinition;
                }

                if ('deriving' === $token[1]) {
                    $token = $skipWhitespace($nextToken());

                    if ($token[1] !== '(') {
                        throw ParseError::unexpectedTokenFound('(', $token, $filename);
                    }

                    $token = $nextToken();

                    while ($token[1] !== ')') {
                        $token = $skipWhitespace($token);
                        $requireString($token);

                        if (! isset($derivingMap[$token[1]])) {
                            throw ParseError::unknownDeriving($token[2], $filename);
                        }

                        $derivingName = $token[1];

                        $derivings[] = $derivingMap[$token[1]];
                        $token = $skipWhitespace($nextToken());

                        if (\in_array($derivingName, ['AggregateChanged', 'Command', 'DomainEvent', 'Query'], true)
                            && ':' === $token[1]
                        ) {
                            $token = $skipWhitespace($nextToken());

                            if (T_CONSTANT_ENCAPSED_STRING !== $token[0]) {
                                throw ParseError::unexpectedTokenFound('T_CONSTANT_ENCAPSED_STRING', $token, $filename);
                            }

                            $messageName = \substr($token[1], 1, -1);

                            $token = $skipWhitespace($nextToken());
                        }

                        if ($token[1] === ',') {
                            $token = $nextToken();
                        }
                    }

                    $token = $skipWhitespace($nextToken());

                    if (';' === $token[1]) {
                        goto buildDefinition;
                    }
                }

                if ('with' === $token[1]) {
                    $enumDerivingFound = false;

                    $key = null;
                    foreach ($derivings as $key => $deriving) {
                        if ($deriving->equals(new Deriving\Enum())) {
                            $enumDerivingFound = true;
                            break;
                        }
                    }

                    if (! $enumDerivingFound) {
                        throw ParseError::unexpectedTokenFound('\'where\' or \';\'', $token, $filename);
                    }

                    $valueMapping = [];
                    $token = $skipWhitespace($nextToken());

                    if ($token[1] !== '(') {
                        throw ParseError::unexpectedTokenFound('(', $token, $filename);
                    }

                    $token = $nextToken();

                    while ($token[1] !== ')') {
                        $token = $skipWhitespace($token);
                        $requireString($token);
                        $enumConstructor = $token[1];
                        $token = $skipWhitespace($nextToken());

                        if ($token[1] !== ':') {
                            throw ParseError::unexpectedTokenFound(':', $token, $filename);
                        }

                        $token = $skipWhitespace($nextToken());

                        $bracesOpened = 0;
                        $code = '';

                        while (true) {
                            if ($token[1] === '[') {
                                ++$bracesOpened;
                            }

                            if ($token[1] === ']') {
                                --$bracesOpened;
                            }

                            $code .= $token[1];
                            $token = $skipWhitespace($nextToken());

                            if (\in_array($code, ['+', '-'], true)) {
                                $code .= $token[1];
                                $token = $skipWhitespace($nextToken());
                            }

                            if (0 === $bracesOpened) {
                                break;
                            }
                        }

                        if (! \in_array($token[1], [',', ')'], true)) {
                            throw ParseError::unexpectedTokenFound(',', $token, $filename);
                        }

                        if ($token[1] !== ')') {
                            $token = $skipWhitespace($nextToken());
                        }

                        if (\in_array(\substr($code, 0, 1), ['\'', '"'], true)) {
                            $code = \substr($code, 1, -1);
                        } else {
                            eval('$code = ' . $code . ';');
                        }

                        $valueMapping[$enumConstructor] = $code;
                    }

                    $token = $skipWhitespace($nextToken());
                    if (null !== $key) {
                        unset($derivings[$key]);
                    }
                    $derivings[] = new Deriving\Enum($valueMapping);
                }

                if ('where' === $token[1]) {
                    $conditionConstructor = '_';
                    $token = $skipWhitespace($nextToken());

                    if (T_STRING === $token[0]) {
                        parseConditionsForConstructor:
                        $conditionConstructor = $token[1];

                        if ($conditionConstructor !== '_'
                            && \substr($conditionConstructor, 0, 1) !== '\\'
                        ) {
                            $conditionConstructor = $namespace . '\\' . $conditionConstructor;
                        }

                        $token = $skipWhitespace($nextToken());

                        if (':' !== $token[1]) {
                            throw ParseError::unexpectedTokenFound(':', $token, $filename);
                        }

                        $token = $skipWhitespace($nextToken());
                    }

                    if ('|' !== $token[1]) {
                        throw ParseError::unexpectedTokenFound('|', $token, $filename);
                    }

                    $token = $skipWhitespace($nextToken());

                    parseCondition:
                    $bracesOpened = 0;
                    $code = '';

                    while (true) {
                        if (\in_array($token[1], ['(', '['], true)) {
                            ++$bracesOpened;
                        }

                        if (\in_array($token[1], [')', ']'], true)) {
                            --$bracesOpened;
                        }

                        if (0 === $bracesOpened && T_DOUBLE_ARROW === $token[0]) {
                            break;
                        }

                        $code .= $token[1];
                        $token = $nextToken();
                    }

                    $token = $skipWhitespace($nextToken());

                    if (T_CONSTANT_ENCAPSED_STRING !== $token[0]) {
                        throw ParseError::unexpectedTokenFound('T_CONSTANT_ENCAPSED_STRING', $token, $filename);
                    }

                    $errorMessage = $token[1];

                    $conditions[] = new Condition($conditionConstructor, \trim($code), \substr($errorMessage, 1, -1));

                    $token = $skipWhitespace($nextToken());

                    if ('|' === $token[1]) {
                        $token = $skipWhitespace($nextToken());
                        goto parseCondition;
                    }

                    if (T_STRING === $token[0]) {
                        goto parseConditionsForConstructor;
                    }
                }

                buildDefinition:
                if (null === $definitionType) {
                    throw ParseError::unknownDefinitionType($namespace, $name);
                }

                $collection->addDefinition(new Definition($definitionType, $namespace, $name, $constructors, $derivings, $conditions, $messageName, $markers));
                break;
            case T_WHITESPACE:
                break;
            case T_OTHER:
                if ($namespaceFound && $token[1] === '}') {
                    $namespaceFound = false;
                    $namespace = '';
                    break;
                }
                throw ParseError::unexpectedTokenFound('T_STRING', $token, $filename);
                break;
            default:
                throw ParseError::unexpectedTokenFound('T_STRING', $token, $filename);
        }

        if ($position < $tokenCount - 1) {
            $token = $nextToken();
        } else {
            ++$position;
        }
    }

    return $collection;
}
