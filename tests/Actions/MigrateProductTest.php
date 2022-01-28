<?php
/**
 * MigrateProductTest.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\Actions;

use EddSlReleases\Actions\Migrations\MigrateProduct;
use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;
use EddSlReleases\Tests\Helpers\Factories\FileFactory;
use EddSlReleases\Tests\Helpers\Factories\ProductFactory;
use EddSlReleases\Tests\Helpers\Factories\ReleaseFactory;
use EddSlReleases\Tests\Helpers\File;
use EddSlReleases\Tests\TestCase;
use EddSlReleases\Tests\Traits\InteractsWithProducts;

/**
 * @coversDefaultClass \EddSlReleases\Actions\Migrations\MigrateProduct
 */
class MigrateProductTest extends TestCase
{
    use InteractsWithProducts;

    protected File $file;
    protected \EDD_SL_Download $product;
    protected ReleaseRepository $releaseRepository;
    protected MigrateProduct $productMigrator;

    public function set_up()
    {
        parent::set_up();

        $this->createProducts();

        $this->releaseRepository = eddSlReleases(ReleaseRepository::class);
        $this->productMigrator   = eddSlReleases(MigrateProduct::class);
    }

    protected function createProducts(): void
    {
        $this->file    = FileFactory::make([
            'name' => 'my-file-2.zip',
        ]);
        $this->product = ProductFactory::make([
            '_edd_sl_version'    => '1.2',
            '_edd_sl_changelog'  => 'This is the release changelog.',
            'edd_download_files' => [
                $this->file->toArray(),
            ]
        ]);

        $this->setProductRequirements(
            $this->product->ID,
            [
                'php' => '7.4',
                'wp'  => '5.6',
            ]
        );
    }

    /**
     * @covers \EddSlReleases\Actions\Migrations\MigrateProduct::execute
     *
     * @return void
     * @throws \EddSlReleases\Exceptions\FileProcessingException
     * @throws \EddSlReleases\Exceptions\ModelNotFoundException
     */
    public function test_migrating_product_without_beta_creates_one_release()
    {
        $releases = $this->releaseRepository->listForProduct($this->product->ID);
        $this->assertEmpty($releases);

        $release = $this->productMigrator->setProductIds(
            $this->releaseRepository->getProductIdsWithReleases()
        )->execute($this->product);

        $this->assertInstanceOf(Release::class, $release);
        $this->assertSame('1.2', $release->version);
        $this->assertSame($this->product->ID, $release->product_id);
        $this->assertSame($this->file->attachmentId, $release->file_attachment_id);
        $this->assertSame($this->file->path, $release->file_path);
        $this->assertSame($this->file->name, $release->file_name);
        $this->assertSame($this->product->get_changelog(), $release->changelog);
        $this->assertFalse($release->pre_release);
        $this->assertSameSetsWithIndex([
            'php' => '7.4',
            'wp'  => '5.6',
        ], $release->requirements);
        $this->assertSame($this->product->post_modified_gmt, $release->released_at);

        $releases = $this->releaseRepository->listForProduct($this->product->ID);
        $this->assertCount(1, $releases);
    }

    /**
     * @covers \EddSlReleases\Actions\Migrations\MigrateProduct::migrateBeta
     *
     * @return void
     * @throws \EddSlReleases\Exceptions\FileProcessingException
     * @throws \EddSlReleases\Exceptions\ModelNotFoundException
     */
    public function test_migrating_product_with_beta_creates_two_releases()
    {
        $releases = $this->releaseRepository->listForProduct($this->product->ID);
        $this->assertEmpty($releases);

        $betaFile = FileFactory::make([
            'file_path' => '/tmp/2.0-beta1.zip',
            'name'      => '2.0-beta1.zip',
            'url'       => 'http://localhost/2.0-beta1.zip',
        ]);
        $this->setProductBeta($this->product->ID, '2.0-beta1');
        $this->setBetaChangelog($this->product->ID, 'Beta changelog.');
        $this->setBetaFile($this->product->ID, $betaFile);

        // Refresh the product to get latest beta stuff.
        $this->product = new \EDD_SL_Download($this->product->ID);
        $this->assertSame('2.0-beta1', $this->product->get_beta_version());

        $release = $this->productMigrator->setProductIds(
            $this->releaseRepository->getProductIdsWithReleases()
        )->execute($this->product);

        $releases = $this->releaseRepository->listForProduct($this->product->ID);
        $this->assertCount(2, $releases);

        $betaReleases = array_filter($releases, function (Release $release) {
            return $release->pre_release;
        });

        $this->assertCount(1, $betaReleases);

        /** @var Release $betaRelease */
        $betaRelease = reset($betaReleases);

        $this->assertInstanceOf(Release::class, $betaRelease);
        $this->assertSame($this->product->get_beta_version(), $betaRelease->version);
        $this->assertSame($this->product->ID, $betaRelease->product_id);
        $this->assertSame($betaFile->attachmentId, $betaRelease->file_attachment_id);
        $this->assertSame($betaFile->path, $betaRelease->file_path);
        $this->assertSame($betaFile->name, $betaRelease->file_name);
        $this->assertSame($this->product->get_beta_changelog(), $betaRelease->changelog);
        $this->assertTrue($betaRelease->pre_release);
        $this->assertSameSetsWithIndex([
            'php' => '7.4',
            'wp'  => '5.6',
        ], $betaRelease->requirements);
        $this->assertSame($this->product->post_modified_gmt, $betaRelease->released_at);
    }

    /**
     * @covers \EddSlReleases\Actions\Migrations\MigrateProduct::dryRun
     * @covers \EddSlReleases\Actions\Migrations\MigrateProduct::execute
     *
     * @return void
     * @throws \EddSlReleases\Exceptions\FileProcessingException
     * @throws \EddSlReleases\Exceptions\ModelNotFoundException
     */
    public function test_migrating_product_as_dry_run_doesnt_create_release()
    {
        $this->assertEmpty(
            $this->releaseRepository->listForProduct($this->product->ID)
        );

        $release = $this->productMigrator->setProductIds(
            $this->releaseRepository->getProductIdsWithReleases()
        )
            ->dryRun(true)
            ->execute($this->product);

        $this->assertNull($release);

        $this->assertEmpty(
            $this->releaseRepository->listForProduct($this->product->ID)
        );
    }

    /**
     * @covers \EddSlReleases\Actions\Migrations\MigrateProduct::validateMigration
     *
     * @return void
     * @throws \EddSlReleases\Exceptions\FileProcessingException
     * @throws \EddSlReleases\Exceptions\ModelNotFoundException
     */
    public function test_migrating_product_that_has_release_throws_exception()
    {
        $release = ReleaseFactory::make([
            'product_id' => $this->product->ID,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product #'.$this->product->ID.' already has releases');

        $this->productMigrator->setProductIds(
            $this->releaseRepository->getProductIdsWithReleases()
        )
            ->execute($this->product);
    }

    /**
     * @covers \EddSlReleases\Actions\Migrations\MigrateProduct::validateMigration
     *
     * @return void
     * @throws \EddSlReleases\Exceptions\FileProcessingException
     * @throws \EddSlReleases\Exceptions\ModelNotFoundException
     */
    public function test_migrating_product_that_doesnt_have_licensing_throws_exception()
    {
        delete_post_meta($this->product->ID, '_edd_sl_enabled');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Licensing is not enabled for product #'.$this->product->ID);

        $this->productMigrator->setProductIds(
            $this->releaseRepository->getProductIdsWithReleases()
        )
            ->execute($this->product);
    }

    /**
     * @covers \EddSlReleases\Actions\Migrations\MigrateProduct::validateMigration
     *
     * @return void
     * @throws \EddSlReleases\Exceptions\FileProcessingException
     * @throws \EddSlReleases\Exceptions\ModelNotFoundException
     */
    public function test_migrating_product_that_doesnt_have_a_file_throws_exception()
    {
        delete_post_meta($this->product->ID, 'edd_download_files');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File key 0 not found in files array for product #'.$this->product->ID);

        $this->productMigrator->setProductIds(
            $this->releaseRepository->getProductIdsWithReleases()
        )
            ->execute($this->product);
    }

}
