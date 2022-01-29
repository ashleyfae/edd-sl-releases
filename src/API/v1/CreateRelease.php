<?php
/**
 * CreateRelease.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\API\v1;

use EddSlReleases\Actions\CreateAndPublishRelease;
use EddSlReleases\Services\ReleaseFileProcessor;
use EddSlReleases\API\RestRoute;
use EddSlReleases\Repositories\ReleaseRepository;
use EddSlReleases\Traits\ChecksPermissions;
use EddSlReleases\Traits\SanitizesRequirements;

class CreateRelease implements RestRoute
{
    use ChecksPermissions, SanitizesRequirements;

    public function __construct(protected CreateAndPublishRelease $releasePublisher)
    {

    }

    public function register(): void
    {
        register_rest_route(
            self::NAMESPACE.'/v1',
            'products/(?P<product_id>\d+)/releases',
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'handle'],
                'permission_callback' => [$this, 'restPermissionCheck'],
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
                            return $this->stripUnsafeCharacters(sanitize_text_field($param));
                        }
                    ],
                    'file_url'     => [
                        'required'          => false,
                        'sanitize_callback' => function ($param, $request, $key) {
                            return esc_url_raw($param);
                        }
                    ],
                    'file_zip'     => [
                        'required' => false,
                    ],
                    'file_name'    => [
                        'required'          => true,
                        'validate_callback' => function ($param, $request, $key) {
                            return ! empty($this->stripUnsafeCharacters($param));
                        },
                        'sanitize_callback' => function ($param, $request, $key) {
                            return $this->stripUnsafeCharacters($param);
                        }
                    ],
                    'changelog'    => [
                        'required'          => false,
                        'validate_callback' => function ($param, $request, $key) {
                            return is_string($param) || is_null($param);
                        },
                        'sanitize_callback' => function ($param, $request, $key) {
                            return empty(trim($param)) ? null : wp_kses_post($param);
                        }
                    ],
                    'pre_release'  => [
                        'required'          => false,
                        'default'           => false,
                        'sanitize_callback' => function ($param, $request, $key) {
                            return filter_var($param, FILTER_VALIDATE_BOOL);
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

                            if (is_string($param)) {
                                // Try to decode it.
                                $param = json_decode($param, true);
                            }

                            if (! is_array($param) && ! is_object($param)) {
                                return false;
                            }

                            $invalidPlatforms = array_diff_key((array) $param, edd_sl_get_platforms());
                            if (! empty($invalidPlatforms)) {
                                return new \WP_Error(
                                    'invalid_requirement_platforms',
                                    sprintf(
                                        /* Translators: %1$s - invalid platform(s), %1$s allowed platforms */
                                        __(
                                            'Invalid requirement platforms: %1$s. Only the following are allowed: %2$s.',
                                            'edd-sl-releases'
                                        ),
                                        json_encode($invalidPlatforms),
                                        json_encode(array_keys(edd_sl_get_platforms()))
                                    )
                                );
                            }

                            return true;
                        },
                        'sanitize_callback' => [$this, 'sanitizeRequirements'],
                    ],
                    'released_at'  => [
                        'required'          => false,
                        'default'           => gmdate('Y-m-d H:i:s'),
                        'validate_callback' => function ($param, $request, $key) {
                            return strtotime($param) !== false;
                        },
                        'sanitize_callback' => function ($param, $request, $key) {
                            return date('Y-m-d H:i:s', strtotime($param));
                        }
                    ]
                ]
            ]
        );
    }

    protected function stripUnsafeCharacters(string $input): string
    {
        return trim(preg_replace('/[^a-z0-9.\-_]/i', '', $input));
    }

    public function handle(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $release = $this->releasePublisher->execute($request->get_params());

            return new \WP_REST_Response($release->toArray(), 201);
        } catch (\Exception $e) {
            edd_debug_log(sprintf('SL Releases - CreateRelease - API error: %s', $e->getMessage()));

            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ? : 500);
        }
    }
}
