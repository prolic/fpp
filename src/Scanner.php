<?php

declare(strict_types=1);

namespace Fpp;

use FilterIterator;

class Scanner extends FilterIterator
{
    /**
     * Create an instance of the locator iterator
     *
     * Expects either a directory, or a DirectoryIterator (or its recursive variant)
     * instance.
     *
     * @param  string|\DirectoryIterator $dirOrIterator
     */
    public function __construct($dirOrIterator)
    {
        if (is_string($dirOrIterator)) {
            if (! is_dir($dirOrIterator)) {
                throw new \InvalidArgumentException('Expected a valid directory name');
            }

            $dirOrIterator = new \RecursiveDirectoryIterator($dirOrIterator);
        }

        if (! $dirOrIterator instanceof \DirectoryIterator) {
            throw new \InvalidArgumentException('Expected a DirectoryIterator');
        }

        if ($dirOrIterator instanceof \RecursiveIterator) {
            $iterator = new \RecursiveIteratorIterator($dirOrIterator);
        } else {
            $iterator = $dirOrIterator;
        }

        parent::__construct($iterator);
    }

    /**
     * Filter for files containing fpp code
     *
     * @return bool
     */
    public function accept()
    {
        $file = $this->getInnerIterator()->current();

        // If we somehow have something other than an SplFileInfo object, just
        // return false
        if (!$file instanceof \SplFileInfo) {
            return false;
        }

        // If we have a directory, it's not a file, so return false
        if (!$file->isFile()) {
            return false;
        }

        return $file->getExtension() === 'fpp';
    }
}
