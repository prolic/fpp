<?php

declare(strict_types=1);

namespace Fpp;

use ArrayIterator;
use FilterIterator;
use Iterator;
use RecursiveDirectoryIterator;
use SplFileInfo;

const scan = '\Fpp\scan';

function scan (string $directoryOrFile): Iterator
{
    if (! is_readable($directoryOrFile)) {
        throw new \RuntimeException("'$directoryOrFile' is not readable");
    }

    $filterIterator = function (Iterator $iterator): FilterIterator {
        return new class($iterator) extends FilterIterator {
            public function accept()
            {
                $file = $this->getInnerIterator()->current();

                if (! $file instanceof \SplFileInfo) {
                    return false;
                }

                if (! $file->isFile()) {
                    return false;
                }

                return $file->getExtension() === 'fpp';
            }
        };
    };

    if (is_file($directoryOrFile)) {
        $iterator = new ArrayIterator();
        $iterator->append(new SplFileInfo($directoryOrFile));

        return $filterIterator($iterator);
    }

    if (! is_dir($directoryOrFile)) {
        throw new \RuntimeException("'$directoryOrFile' is not a directory or file");
    }

    $iterator = new RecursiveDirectoryIterator($directoryOrFile);

    return $filterIterator($iterator);
}
