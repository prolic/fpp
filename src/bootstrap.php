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

$autoloadFiles = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
];

$autoloader = null;
foreach ($autoloadFiles as $autoloadFile) {
    if (\file_exists($autoloadFile)) {
        $autoloader = require_once $autoloadFile;
        break;
    }
}

if (! $autoloader) {
    throw new \RuntimeException(\sprintf(
        'Unable to locate autoloader in "%s"',
        \implode(', ', $autoloadFiles)
    ));
}

$files = [
    'dump.php',
    'helpers.php',
    'loadTemplate.php',
    'locatePsrPath.php',
    'parse.php',
    'replace.php',
    'scan.php',
    'builder/buildAccessors.php',
    'builder/buildArgumentName.php',
    'builder/buildArguments.php',
    'builder/buildClassExtends.php',
    'builder/buildClassKeyword.php',
    'builder/buildClassName.php',
    'builder/buildConstructor.php',
    'builder/buildEnumConstructors.php',
    'builder/buildEnumConsts.php',
    'builder/buildEnumOptions.php',
    'builder/buildEqualsBody.php',
    'builder/buildFromArrayBody.php',
    'builder/buildFromScalarBody.php',
    'builder/buildFromStringBody.php',
    'builder/buildMessageName.php',
    'builder/buildNamespace.php',
    'builder/buildPayloadValidation.php',
    'builder/buildProperties.php',
    'builder/buildScalarConstructorConditions.php',
    'builder/buildScalarType.php',
    'builder/buildSetters.php',
    'builder/buildStaticConstructor.php',
    'builder/buildStaticConstructorBody.php',
    'builder/buildToArrayBody.php',
    'builder/buildToScalarBody.php',
    'builder/buildTraits.php',
    'builder/buildVariableName.php',
    'builder/buildInterfaceName.php',
    'builder/buildClassImplements.php',
];

foreach ($files as $file) {
    require_once __DIR__ . '/' . $file;
}

return $autoloader;
