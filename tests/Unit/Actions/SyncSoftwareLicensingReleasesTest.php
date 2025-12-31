<?php
/**
 * SyncSoftwareLicensingReleasesTest.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace EddSlReleases\Tests\Unit\Actions;

use EddSlReleases\Actions\SyncSoftwareLicensingReleases;
use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;
use EddSlReleases\Tests\Unit\TestCase;
use WP_Mock;

class SyncSoftwareLicensingReleasesTest extends TestCase
{
    /**
     * @covers       \EddSlReleases\Actions\SyncSoftwareLicensingReleases::updateProductDownloads()
     * @dataProvider providerCanUpdateProductDownloads
     */
    public function testCanUpdateProductDownloads(bool $isPreRelease)
    {
        WP_Mock::userFunction('edd_debug_log');

        $action = new SyncSoftwareLicensingReleases(\Mockery::mock(ReleaseRepository::class));

        $release                     = \Mockery::mock(Release::class);
        $release->id                 = 1;
        $release->pre_release        = $isPreRelease;
        $release->file_name          = 'my-file';
        $release->file_attachment_id = 123;
        $release->product_id         = 56;
        $release->expects('getProtectedFileUrl')->once()->andReturn('https://fileurl.example.com/my-file.zip');

        $existingFiles = [
            0 => [
                'index'          => '0',
                'attachment_id'  => '53491',
                'thumbnail_size' => false,
                'name'           => 'book-database-1.3',
                'file'           => 'https://example.com/file.zip',
                'condition'      => 'all',
            ],
        ];

        $product = \Mockery::mock('EDD_SL_Download');

        $product->expects('get_ID')->andReturn(123);

        $product->expects('get_beta_files')
            ->times($isPreRelease ? 1 : 0)
            ->andReturn($existingFiles);
        $product->expects('get_files')
            ->times($isPreRelease ? 0 : 1)
            ->andReturn($existingFiles);

        $product->expects('get_beta_upgrade_file_key')
            ->times($isPreRelease ? 1 : 0)
            ->andReturn('0');

        $product->expects('get_upgrade_file_key')
            ->times($isPreRelease ? 0 : 1)
            ->andReturn('0');

        $filesMetaKey = $isPreRelease ? '_edd_sl_beta_files' : 'edd_download_files';
        $fileKeyMeta = $isPreRelease ? '_edd_sl_beta_upgrade_file_key' : '_edd_sl_upgrade_file_key';

        $expectedFiles = [
            0 => [
                'index'         => '0',
                'name'          => 'my-file',
                'file'          => 'https://fileurl.example.com/my-file.zip',
                'condition'     => 'all',
                'attachment_id' => 123,
            ],
        ];

        WP_Mock::userFunction('update_post_meta')
            ->once()
            ->with(56, $filesMetaKey, $expectedFiles);

        WP_Mock::userFunction('update_post_meta')
            ->once()
            ->with(56, $fileKeyMeta, '0');

        $this->invokeInaccessibleMethod($action, 'updateProductDownloads', $release, $product);

        $this->assertConditionsMet();
    }

    /** @see testCanUpdateProductDownloads */
    public function providerCanUpdateProductDownloads(): \Generator
    {
        yield 'pre release' => [true];
        yield 'stable release' => [false];
    }
}
