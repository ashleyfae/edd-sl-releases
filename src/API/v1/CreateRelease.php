<?php
/**
 * CreateRelease.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\API\v1;

use EddSlReleases\Services\ReleaseFileProcessor;
use EddSlReleases\API\RestRoute;
use EddSlReleases\Repositories\ReleaseRepository;

class CreateRelease implements RestRoute
{
    protected ReleaseRepository $releaseRepository;
    protected ReleaseFileProcessor $processReleaseFile;

    public function __construct(ReleaseRepository $releaseRepository, ReleaseFileProcessor $processReleaseFile)
    {
        $this->releaseRepository  = $releaseRepository;
        $this->processReleaseFile = $processReleaseFile;
    }

    public function register(): void
    {
        register_rest_route(
            self::NAMESPACE.'/v1',
            'releases',
            [
                'methods'             => \WP_REST_Server::CREATABLE,
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
                    'version'      => [
                        'required'          => true,
                        'sanitize_callback' => function ($param, $request, $key) {
                            return (string) $param;
                        }
                    ],
                    'file_url'     => [
                        'required'          => true,
                        'sanitize_callback' => function ($param, $request, $key) {
                            return esc_url_raw($param);
                        }
                    ],
                    'file_name'    => [
                        'required'          => true,
                        'sanitize_callback' => function ($param, $request, $key) {
                            return sanitize_text_field(wp_strip_all_tags($param));
                        }
                    ],
                    'changelog'    => [
                        'required'          => false,
                        'validate_callback' => function ($param, $request, $key) {
                            return is_string($param);
                        },
                        'sanitize_callback' => function ($param, $request, $key) {
                            return wp_kses_post($param);
                        }
                    ],
                    'requirements' => [
                        'required'          => false,
                        'default'           => null,
                        'validate_callback' => function ($param, $request, $key) {
                            // Empty values are allowed.
                            if (is_null($param) || (is_array($param) && empty($param))) {
                                return true;
                            }

                            if (! is_array($param) && ! is_object($param)) {
                                return false;
                            }

                            $invalidPlatforms = array_diff_key($param, edd_sl_get_platforms());
                            if (! empty($invalidPlatforms)) {
                                return new \WP_Error(
                                    'invalid_requirement_platforms',
                                    sprintf(
                                        __(
                                            'Invalid requirement platforms: %s. Only the following are allowed: %s.',
                                            'edd-sl-releases'
                                        ),
                                        json_encode($invalidPlatforms),
                                        json_encode(array_keys(edd_sl_get_platforms()))
                                    )
                                );
                            }

                            return true;
                        },
                        'sanitize_callback' => function ($param, $request, $key) {
                            $value = is_array($param) && ! empty($param)
                                ? array_intersect_key((array) $param, edd_sl_get_platforms())
                                : null;

                            return $value ?: null;
                        }
                    ]
                ]
            ]
        );
    }

    public function permissionCheck()
    {
        if (! current_user_can('edit_products')) {
            return new \WP_Error(
                'rest_forbidden',
                __('You do not have permission to perform this action.', 'edd-sl-releases'),
                ['status' => is_user_logged_in() ? 403 : 401]
            );
        }

        return true;
    }

    public function handle(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $args             = $request->get_params();
            $args['file_url'] = $this->processReleaseFile->execute(
                $request->get_param('file_url'),
                $request->get_param('file_name')
            );

            $release = $this->releaseRepository->insert($args);

            return new \WP_REST_Response($release->toArray(), 201);
        } catch (\Exception $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 500);
        }
    }
}
