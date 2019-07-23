<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest;

use function Fpp\scan;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class ScanTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::create([
            'Bar' => [
                'fpp1.fpp' => '',
                'Baz' => [
                    'fpp2.fpp' => '',
                ],
                'empty' => [],
            ],
            'fpp3.fpp' => '',
        ], vfsStream::setup('foo'));

        /** @var \org\bovigo\vfs\vfsStreamContainer */
        $bar = $this->root->getChild('Bar');
        $file = $bar->getChild('fpp1.fpp');
        $file->chmod(0000);
    }

    /**
     * @test
     */
    public function it_detects_readable_file(): void
    {
        $list = scan($this->root->getChild('foo/Bar/Baz/fpp2.fpp')->url());

        $this->assertCount(1, $list);
        $this->assertSame('vfs://foo/Bar/Baz/fpp2.fpp', $list[0]);
    }

    /**
     * @test
     */
    public function it_detects_all_readable_files_in_directory(): void
    {
        $list = scan($this->root->url());

        $this->assertSame(
            [
                'vfs://foo/Bar/Baz/fpp2.fpp',
                'vfs://foo/fpp3.fpp',
            ],
            $list
        );
    }

    /**
     * @test
     */
    public function it_throws_if_target_is_not_readable(): void
    {
        $this->expectException(\RuntimeException::class);

        scan('vfs://foo/Bar/fpp1.fpp');
    }

    /**
     * @test
     */
    public function it_throws_if_no_results_found(): void
    {
        $this->expectException(\RuntimeException::class);

        scan('vfs://foo/Bar/empty');
    }
}
