<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppSpec;

use function describe;
use function expect;
use Fpp\Argument;
use function Fpp\buildDefaultPhpFile;
use function Fpp\calculateDefaultValue;
use Fpp\Configuration;
use Fpp\Definition;
use function Fpp\flatMap;
use function Fpp\isKeyword;
use function Fpp\locatePsrPath;
use Fpp\Type\Enum\Constructor;
use Fpp\Type\Enum\Enum;
use Nette\PhpGenerator\PsrPrinter;
use function Pair;

describe('Fpp', function () {
    context('Basic FPP helper functions', function () {
        describe('isKeyword', function () {
            it('returns true for any given php keyword', function () {
                expect(isKeyword('final'))->toBe(true);
            });

            it('returns false for any given non-keyword string', function () {
                expect(isKeyword('somestring'))->toBe(false);
            });

            it('returns false for an emptry string', function () {
                expect(isKeyword(''))->toBe(false);
            });
        });

        describe('flatMap', function () {
            it('flattens an array', function () {
                $fn = function ($x) {
                    if (\is_array($x)) {
                        $res = [];

                        foreach ($x as $y) {
                            $res[] = $y + 1;
                        }

                        return $res;
                    }

                    return $x + 1;
                };

                $input = [1, 2, [3, 4]];

                expect(flatMap($fn, $input))->toBe([2, 3, 4, 5]);
            });
        });

        describe('locatePsrPath', function () {
            it('can locate filename for psr4-autoloaded class', function () {
                $psr4 = [
                    'Foo\\' => ['src'],
                ];

                expect(locatePsrPath($psr4, [], 'Foo\\Bar'))->toBe('src/Bar.php');
            });

            it('can locate filename for psr0-autoloaded class', function () {
                $psr0 = [
                    'Foo\\' => ['src/Foo'],
                ];

                expect(locatePsrPath([], $psr0, 'Foo\\Bar'))->toBe('src/Foo/Bar.php');
            });
        });

        describe('buildDefaultPhpFile', function () {
            it('can build default php file structure', function () {
                $definition = new Definition(
                    'Foo',
                    new Enum('Color', [], [
                        new Constructor('Blue'),
                        new Constructor('Red'),
                    ]),
                    [
                        Pair('LongName', 'LN'),
                    ]
                );
                $config = new Configuration(
                    true,
                    fn () => new PsrPrinter(),
                    fn () => null,
                    null,
                    []
                );

                $phpFile = buildDefaultPhpFile($definition, $config);

                expect($phpFile->hasStrictTypes())->toBe(true);
                expect($phpFile->getNamespaces())->not->toBeEmpty();
                expect($phpFile->getNamespaces()['Foo']->getName())->toBe('Foo');
                expect($phpFile->getNamespaces()['Foo']->getUses())->toEqual(['LN' => 'LongName']);
            });
        });

        describe('calculateDefaultValue', function () {
            it('can calculate default php values for a given argument', function () {
                $arg1 = new Argument('foo', null, false, false, null);

                expect(calculateDefaultValue($arg1))->toBe(null);

                $arg2 = new Argument('foo', 'string', false, false, '\'\'');

                expect(calculateDefaultValue($arg2))->toBe('');

                $arg3 = new Argument('foo', 'int', false, false, '12');

                expect(calculateDefaultValue($arg3))->toBe(12);

                $arg4 = new Argument('foo', null, false, true, '[]');

                expect(calculateDefaultValue($arg4))->toBe([]);

                $arg5 = new Argument('foo', 'float', false, false, '12.3');

                expect(calculateDefaultValue($arg5))->toBe(12.3);

                $arg6 = new Argument('foo', 'string', false, false, '\'foo\'');

                expect(calculateDefaultValue($arg6))->toBe('foo');

                $arg7 = new Argument('foo', null, false, false, null);

                expect(calculateDefaultValue($arg7))->toBe(null);

                $arg8 = new Argument('foo', 'array', false, true, '[]');

                expect(calculateDefaultValue($arg8))->toBe([]);

                $arg9 = new Argument('foo', 'string', true, false, null);

                expect(calculateDefaultValue($arg9))->toBe(null);
            });
        });
    });
});
