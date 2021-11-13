<?php
/**
 * ApiTestCase.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\API;

use EddSlReleases\Services\ReleaseFileProcessor;
use EddSlReleases\API\RestRoute;
use EddSlReleases\Tests\TestCase;
use EddSlReleases\Tests\Traits\InteractsWithProducts;
use Mockery\MockInterface;

class ApiTestCase extends TestCase
{
    use InteractsWithProducts;

    protected static \WP_REST_Server $server;

    protected \EDD_SL_Download $product;

    protected static MockInterface $processReleaseFile;

    public static function set_up_before_class()
    {
        parent::set_up_before_class();

        global $wp_rest_server;
        self::$server = $wp_rest_server = new \WP_REST_Server();

        // This needs to run before `rest_api_init`.
        self::$processReleaseFile = \Mockery::mock(ReleaseFileProcessor::class);
        eddSlReleases()->instance(ReleaseFileProcessor::class, self::$processReleaseFile);

        do_action('rest_api_init');
    }

    public function set_up()
    {
        parent::set_up();

        $this->product = $this->createProduct();
    }

    protected function makeRestRequest(
        string $endpointUri,
        array $payload = [],
        string $method = \WP_REST_Server::CREATABLE
    ): \WP_REST_Response {
        $request = new \WP_REST_Request($method, sprintf(
            '/%s/%s',
            RestRoute::NAMESPACE,
            $endpointUri
        ));

        $request->set_header('Content-Type', 'application/json');

        if (! empty($payload)) {
            $request->set_body(json_encode($payload));
        }

        return self::$server->dispatch($request);
    }

}
