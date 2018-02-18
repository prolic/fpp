<?php

declare(strict_types=1);

namespace Fpp;

use FilterIterator;
use Phunkie\Types\ImmList;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

const scan = '\Fpp\scan';

function scan(string $directoryOrFile): ImmList
{
    if (! is_readable($directoryOrFile)) {
        throw new RuntimeException("'$directoryOrFile' is not readable");
    }

    if (is_file($directoryOrFile)) {
        return ImmList($directoryOrFile);
    }

    $iterator = new class(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directoryOrFile))) extends FilterIterator {
        public function __construct($directoryOrFile)
        {
            parent::__construct($directoryOrFile);
        }

        public function accept()
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

    return ImmList(...$files);
}