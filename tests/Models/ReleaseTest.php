<?php
/**
 * ReleaseTest.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\Models;

use EddSlReleases\Models\Release;
use EddSlReleases\Tests\TestCase;

class ReleaseTest extends TestCase
{

    /**
     * @covers \EddSlReleases\Traits\Serializable::toArray
     * @covers \EddSlReleases\Traits\Serializable::toJson
     */
    public function test_to_array_returns_public_properties()
    {
        $args = [
            'id'           => 1,
            'product_id'   => 123,
            'version'      => '1.0',
            'file_url'     => 'https://example.com',
            'changelog'    => null,
            'requirements' => null,
            'pre_release'  => false,
            'created_at'   => '2021-11-13 12:23:00',
        ];

        $release = new Release($args);

        $this->assertSame(json_encode($args), $release->toJson());
    }

    /**
     * @covers \EddSlReleases\Traits\CastsAttributes::castAttribute
     */
    public function test_integer_cast_to_boolean()
    {
        $release = new Release(['pre_release' => 1]);
        $this->assertIsBool($release->pre_release);
        $this->assertTrue($release->pre_release);
    }

    /**
     * @covers \EddSlReleases\Traits\CastsAttributes::castAttribute
     */
    public function test_string_cast_to_integer()
    {
        $release = new Release(['product_id' => '123']);
        $this->assertSame(123, $release->product_id);
    }

    /**
     * @covers \EddSlReleases\Traits\CastsAttributes::castAttribute
     */
    public function test_json_cast_to_array()
    {
        $requirements = [
            'php' => '7.4',
            'wp'  => '5.8',
        ];

        $release = new Release(['requirements' => json_encode($requirements)]);
        $this->assertIsArray($release->requirements);
        $this->assertequalscanonicalizing($requirements, $release->requirements);
    }

}
