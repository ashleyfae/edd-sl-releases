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
use EddSlReleases\ValueObjects\PreparedReleaseFile;

/**
 * @coversDefaultClass \EddSlReleases\API\v1\CreateRelease
 */
class CreateReleaseTest extends ApiTestCase
{
    private function getApiEndpoint(): string
    {
        return 'v1/products/'.$this->product->ID.'/releases';
    }

    public function test_request_with_empty_payload_throws_400()
    {
        $response = $this->makeRestRequest($this->getApiEndpoint(), []);

        $this->assertEquals(400, $response->get_status());
    }

    public function test_invalid_requirement_throws_400()
    {
        $response = $this->makeRestRequest($this->getApiEndpoint(), [
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
        $response = $this->makeRestRequest($this->getApiEndpoint(), [
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
    public function test_valid_args_with_file_url_creates_new_release()
    {
        wp_set_current_user(1);

        self::$processReleaseFile->shouldReceive('executeFromGitAsset')
            ->once()
            ->andReturn(new PreparedReleaseFile('/var/www/mysite.com/file.zip', 1));

        $response = $this->makeRestRequest($this->getApiEndpoint(), [
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
        $this->assertSame(1, $release->file_attachment_id);
        $this->assertSame('/var/www/mysite.com/file.zip', $release->file_path);
        $this->assertNull($release->changelog);
        $this->assertSame(['php' => '7.4'], $release->requirements);
        $this->assertFalse($release->pre_release);
    }

}
