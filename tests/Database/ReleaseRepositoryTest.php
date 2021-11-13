<?php
/**
 * ReleaseRepositoryTest.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\Database;

use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;
use EddSlReleases\Tests\TestCase;
use EddSlReleases\Tests\Traits\InteractsWithProducts;

/**
 * @coversDefaultClass  \EddSlReleases\Repositories\ReleaseRepository
 */
class ReleaseRepositoryTest extends TestCase
{
    use InteractsWithProducts;

    protected ReleaseRepository $releaseRepository;
    protected \EDD_SL_Download $product;

    public function set_up()
    {
        parent::set_up();

        $this->releaseRepository = eddSlReleases(ReleaseRepository::class);
        $this->product           = $this->createProduct();
    }

    /**
     * @covers \EddSlReleases\Repositories\ReleaseRepository::insert
     * @throws \EddSlReleases\Exceptions\ModelNotFoundException
     */
    public function test_insert_release_adds_release_to_db()
    {
        $release = $this->releaseRepository->insert([
            'product_id' => $this->product->ID,
            'version'    => '1.5',
            'file_url'   => 'https://sampleurl.com',
        ]);

        $this->assertInstanceOf(Release::class, $release);

        $this->assertSame($this->product->ID, $release->product_id);
        $this->assertSame('1.5', $release->version);
    }

    /**
     * @covers \EddSlReleases\Repositories\ReleaseRepository::insert
     * @throws \EddSlReleases\Exceptions\ModelNotFoundException|\Exception
     */
    public function test_inserting_release_without_product_id_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Missing required value: product_id");

        $this->releaseRepository->insert([
            'version'  => '1.5',
            'file_url' => 'https://sampleurl.com',
        ]);
    }

}
