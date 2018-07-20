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

use FilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

const scan = '\Fpp\scan';

/**
 * @param string $directoryOrFile
 * @return string[]
 */
function scan(string $directoryOrFile): array
{
    if (! \is_readable($directoryOrFile)) {
        throw new RuntimeException("'$directoryOrFile' is not readable");
    }

    if (\is_file($directoryOrFile)) {
        return [$directoryOrFile];
    }

    $iterator = new class(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directoryOrFile))) extends FilterIterator {
        public function __construct($directoryOrFile)
        {
            parent::__construct($directoryOrFile);
        }

        public function accept(): bool
        {
            $file = $this->getInnerIterator()->current();

            if (! $file->isFile()) {
                return false;
            }

            if (! $file->isReadable()) {
                return false;
            }

            return $file->getExtension() === 'fpp';
        }
    };

    $files = [];

    foreach ($iterator as $file) {
        /* @var SplFileInfo $file */
        $files[] = $file->getPathname();
    }

    if (empty($files)) {
        throw new RuntimeException("No .fpp files found in '$directoryOrFile'");
    }

    return $files;
}
