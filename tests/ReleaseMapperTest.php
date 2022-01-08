<?php
/**
 * ReleaseMapperTest.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests;

use EddSlReleases\Database\ReleasesTable;
use EddSlReleases\Tests\Helpers\Factories\ReleaseFactory;

class ReleaseMapperTest extends TestCase
{
    protected \EDD_SL_Download $product;

    public function set_up()
    {
        parent::set_up();

        $productId = wp_insert_post([
            'post_title'  => 'Test Download Product',
            'post_name'   => 'test-download-product',
            'post_type'   => 'download',
            'post_status' => 'publish',
        ]);

        $this->product = new \EDD_SL_Download($productId);
    }

    public function tear_down()
    {
        parent::tear_down();

        wp_delete_post($this->product->ID);

        eddSlReleases(ReleasesTable::class)->truncate();
    }

    /**
     * @covers \EddSlReleases\Helpers\ReleaseMapper::mapBetaData
     * @return void
     */
    public function test_product_without_pre_release_has_no_beta()
    {
        $release = ReleaseFactory::make([
            'product_id' => $this->product->ID,
        ]);

        $this->assertFalse($this->product->has_beta());
    }

    /**
     * @covers \EddSlReleases\Helpers\ReleaseMapper::mapBetaData
     * @return void
     */
    public function test_product_with_pre_release_has_beta()
    {
        $release = ReleaseFactory::make([
            'product_id'  => $this->product->ID,
            'pre_release' => 1,
            'version'     => '3.0-beta1',
            'changelog'   => 'Beta version changelog.',
        ]);

        $this->assertTrue($this->product->has_beta());
        $this->assertSame('3.0-beta1', $this->product->get_beta_version());
        $this->assertSame('Beta version changelog.', $this->product->get_beta_changelog());
    }

    /**
     * @covers \EddSlReleases\Helpers\ReleaseMapper::mapBetaData
     * @return void
     */
    public function test_product_with_release_and_higher_pre_release_has_beta()
    {
        $release = ReleaseFactory::make([
            'product_id' => $this->product->ID,
            'version'    => '2.0',
        ]);

        $preRelease = ReleaseFactory::make([
            'product_id'  => $this->product->ID,
            'pre_release' => 1,
            'version'     => '3.0-beta1',
            'changelog'   => 'Beta version changelog.',
        ]);

        $this->assertTrue($this->product->has_beta());
        $this->assertSame('3.0-beta1', $this->product->get_beta_version());
        $this->assertSame('Beta version changelog.', $this->product->get_beta_changelog());
    }

    /**
     * @covers \EddSlReleases\Helpers\ReleaseMapper::mapBetaData
     * @return void
     */
    public function test_product_with_release_and_lower_pre_release_has_no_beta()
    {
        $release = ReleaseFactory::make([
            'product_id' => $this->product->ID,
            'version'    => '2.0',
        ]);

        $preRelease = ReleaseFactory::make([
            'product_id'  => $this->product->ID,
            'pre_release' => 1,
            'version'     => '1.0-beta1',
            'changelog'   => 'Beta version changelog.',
        ]);

        $this->assertFalse($this->product->has_beta());
    }

}
