<?php
/**
 * CreateReleaseTest.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\API;

use EddSlReleases\Repositories\ReleaseRepository;

/**
 * @coversDefaultClass \EddSlReleases\API\v1\CreateRelease
 */
class CreateReleaseTest extends ApiTestCase
{
    public function test_request_with_empty_payload_throws_400()
    {
        $response = $this->makeRestRequest('v1/releases', []);

        $this->assertEquals(400, $response->get_status());
    }

    public function test_invalid_requirement_throws_400()
    {
        $response = $this->makeRestRequest('v1/releases', [
            'product_id'   => $this->product->ID,
            'version'      => '1.0',
            'file_url'     => 'https://sample-url.com',
            'file_name'    => 'sample-file.zip',
            'requirements' => [
                'invalid_requirement' => '1.0',
            ]
        ]);

        $this->assertEquals(400, $response->get_status());
        $this->assertSame(
            'invalid_requirement_platforms',
            $response->get_data()['data']['details']['requirements']['code']
        );
    }

    /**
     * @covers \EddSlReleases\API\v1\CreateRelease::permissionCheck
     */
    public function test_unauthenticated_request_returns_401()
    {
        $response = $this->makeRestRequest('v1/releases', [
            'product_id'   => $this->product->ID,
            'version'      => '1.0',
            'file_url'     => 'https://sample-url.com',
            'file_name'    => 'sample-file.zip',
            'requirements' => [
                'php' => '7.4',
            ]
        ]);

        $this->assertEquals(401, $response->get_status());
    }

    /**
     * @covers \EddSlReleases\API\v1\CreateRelease::permissionCheck
     * @covers \EddSlReleases\API\v1\CreateRelease::handle
     */
    public function test_valid_args_with_authentication_creates_new_release()
    {
        wp_set_current_user(1);

        self::$processReleaseFile->shouldReceive('execute')
            ->once()
            ->andReturn('https://mysite.com/file.zip');

        $response = $this->makeRestRequest('v1/releases', [
            'product_id'   => $this->product->ID,
            'version'      => '1.0',
            'file_url'     => 'https://sample-url.com',
            'file_name'    => 'sample-file.zip',
            'requirements' => [
                'php' => '7.4',
            ]
        ]);

        $this->assertEquals(201, $response->get_status());

        $release = eddSlReleases(ReleaseRepository::class)->getLatestStableRelease($this->product->ID);

        $this->assertSame('1.0', $release->version);
        $this->assertSame('https://mysite.com/file.zip', $release->file_url);
        $this->assertNull($release->changelog);
        $this->assertSame(['php' => '7.4'], $release->requirements);
        $this->assertFalse($release->pre_release);
    }

}
