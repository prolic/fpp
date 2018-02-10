<?php

declare(strict_types=1);

namespace Fpp;

use FilterIterator;

class Scanner extends FilterIterator
{
    public function __construct(string $path)
    {
        if (is_file($path)) {
            if (! is_readable($path)) {
                throw new \RuntimeException('path is not readable');
            }

            $iterator = new \ArrayIterator();
            $iterator->append(new \SplFileInfo($path));
        } elseif (is_dir($path)) {
            $iterator = new \RecursiveDirectoryIterator($path);
        } else {
            throw new \RuntimeException('path is not a directory or file');
        }

        parent::__construct($iterator);
    }

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
}
