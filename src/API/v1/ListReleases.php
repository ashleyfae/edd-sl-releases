<?php
/**
 * ListReleases.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\API\v1;

use EddSlReleases\API\RestRoute;
use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;
use EddSlReleases\Traits\ChecksPermissions;

class ListReleases implements RestRoute
{
    use ChecksPermissions;

    public function __construct(protected ReleaseRepository $releaseRepository)
    {

    }

    public function register(): void
    {
        register_rest_route(
            self::NAMESPACE.'/v1',
            'products/(?P<product_id>\d+)/releases',
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'handle'],
                'permission_callback' => [$this, 'permissionCheck'],
                'args'                => [
                    'product_id'   => [
                        'required'          => true,
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param) && get_post_type($param) === 'download';
                        },
                        'sanitize_callback' => function ($param, $request, $key) {
                            return intval($param);
                        }
                    ],
                    'pre_releases' => [
                        'default'           => null,
                        'required'          => false,
                        'validate_callback' => function ($param, $request, $key) {
                            return in_array($param, [true, false, null], true);
                        },
                        'sanitize_callback' => function ($param, $request, $key) {
                            return is_null($param) ? null : filter_var($param, FILTER_VALIDATE_BOOL);
                        }
                    ],
                    'offset'       => [
                        'default'           => 0,
                        'required'          => false,
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'sanitize_callback' => function ($param, $request, $key) {
                            return absint($param);
                        }
                    ]
                ]
            ]
        );
    }

    public function handle(\WP_REST_Request $request): \WP_REST_Response
    {
        $releases = $this->releaseRepository->listForProduct(
            $request->get_param('product_id'),
            $request->get_param('pre_releases'),
            10,
            $request->get_param('offset')
        );

        return new \WP_REST_Response([
            'releases' => array_map(function (Release $release) {
                return $release->toArray();
            }, $releases)
        ], 200);
    }
}
