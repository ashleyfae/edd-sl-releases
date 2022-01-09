<?php
/**
 * AppServiceProvider.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ServiceProviders;

use EddSlReleases\Services\Shortcodes;

class AppServiceProvider implements ServiceProvider
{

    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->registerShortcodes();
    }

    protected function registerShortcodes(): void
    {
        $shortcodes = [
            Shortcodes\ReleasesShortcode::class,
        ];

        foreach ($shortcodes as $shortcode) {
            if (! in_array(Shortcodes\ShortcodeInterface::class, class_implements($shortcode), true)) {
                throw new \RuntimeException(sprintf(
                    'The %s shortcode must implement the %s interface.',
                    $shortcode,
                    Shortcodes\ShortcodeInterface::class
                ));
            }

            add_shortcode($shortcode::tag(), static function ($atts, $content = null) use ($shortcode) {
                return eddSlReleases()->make($shortcode)->render((array) $atts, $content);
            });
        }
    }
}
