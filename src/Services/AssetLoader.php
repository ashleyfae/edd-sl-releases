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

        wp_register_style(
            'edd-sl-releases',
            $this->assetUrl('assets/build/css/admin.css'),
            [],
            Plugin::VERSION
        );

        if ($this->shouldEnqueueAdmin()) {
            wp_enqueue_script('edd-sl-releases');
            wp_localize_script(
                'edd-sl-releases',
                'eddSlReleases',
                [
                    'restBase'          => rest_url(RestRoute::NAMESPACE.'/v1/'),
                    'restNonce'         => wp_create_nonce('wp_rest'),
                    'changelog'         => esc_html__('Changelog', 'edd-sl-releases'),
                    'defaultError'      => esc_html__(
                        'An unexpected error has occurred. Please try again.',
                        'edd-sl-releases'
                    ),
                    'edit'              => esc_html__('Edit', 'edd-sl-releases'),
                    'uploadReleaseFile' => esc_html__('Upload or Select a Release File', 'edd-sl-releases'),
                    'selectFile'        => esc_html__('Select File', 'edd-sl-releases'),
                    'preRelease'        => esc_html__('Pre-release', 'edd-sl-releases'),
                    'stableRelease'     => esc_html__('Stable', 'edd-sl-releases'),
                ]
            );
            wp_enqueue_style('edd-sl-releases');
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

        if (edd_is_admin_page('download', 'edit') || edd_is_admin_page('download', 'new')) {
            return true;
        }

        $screen = get_current_screen();
        if (! $screen instanceof \WP_Screen) {
            return false;
        }

        return $screen->id === 'download_page_edd-sl-releases';
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
