<?php
/**
 * AssetLoader.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Services;

use EddSlReleases\API\RestRoute;
use EddSlReleases\Plugin;
use EddSlReleases\Services\Shortcodes\ReleasesShortcode;

class AssetLoader
{

    /**
     * Registers/enqueues frontend assets.
     *
     * @since 1.0
     *
     * @return void
     */
    public function frontend(): void
    {
        wp_register_style(
            'edd-sl-releases',
            $this->assetUrl('assets/build/css/frontend.css'),
            [],
            Plugin::VERSION
        );

        if ($this->shouldEnqueueFrontend()) {
            wp_enqueue_style('edd-sl-releases');
        }
    }

    public function admin(): void
    {
        wp_register_script(
            'edd-sl-releases',
            $this->assetUrl('assets/build/js/admin.js'),
            [],
            Plugin::VERSION,
            true
        );

        if ($this->shouldEnqueueAdmin()) {
            wp_enqueue_script('edd-sl-releases');
            wp_localize_script(
                'edd-sl-releases',
                'eddSlReleases',
                [
                    'restBase'        => rest_url(RestRoute::NAMESPACE.'/v1/'),
                    'restNonce'       => wp_create_nonce('wp_rest'),
                    'changelog'       => esc_html__('Changelog', 'edd-sl-releases'),
                    'defaultError'    => esc_html__(
                        'An unexpected error has occurred. Please try again.',
                        'edd-sl-releases'
                    ),
                    'loadingReleases' => esc_html__('Loading releases...', 'edd-sl-releases'),
                    'noReleases'      => esc_html__('No releases yet.', 'edd-sl-releases'),
                    'preRelease'      => esc_html__('Pre Release', 'edd-sl-releases'),
                ]
            );
        }
    }

    /**
     * Determines if we should enqueue the front-end assets.
     * We only want to do it if our shortcode is being used.
     *
     * @since 1.0
     *
     * @return bool
     */
    private function shouldEnqueueFrontend(): bool
    {
        global $post;

        $shouldEnqueue = $post instanceof \WP_Post && has_shortcode($post->post_content, ReleasesShortcode::tag());

        /**
         * Filters whether we should enqueue the frontend assets.
         *
         * @since 1.0
         *
         * @param  bool  $shouldEnqueue
         * @param  \WP_Post|false  $post
         */
        return apply_filters('edd-sl-releases/assets/enqueue-frontend', $shouldEnqueue, $post);
    }

    private function shouldEnqueueAdmin(): bool
    {
        if (! function_exists('edd_is_admin_page')) {
            return false;
        }

        return edd_is_admin_page('download', 'edit') || edd_is_admin_page('download', 'new');
    }

    /**
     * Builds a URL for a given asset path.
     *
     * @since 1.0
     *
     * @param  string  $filePath  Relative path to the file.
     *
     * @return string
     */
    private function assetUrl(string $filePath): string
    {
        return plugins_url($filePath, EDD_SL_RELEASES_FILE);
    }

}
