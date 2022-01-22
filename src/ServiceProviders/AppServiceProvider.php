<?php
/**
 * AppServiceProvider.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ServiceProviders;

use EddSlReleases\Actions\Admin\CreateRelease;
use EddSlReleases\Actions\Admin\UpdateRelease;
use EddSlReleases\Actions\DownloadReleaseFile;
use EddSlReleases\Admin\AdminPage;
use EddSlReleases\Admin\ProductMetabox;
use EddSlReleases\Helpers\Hooks;
use EddSlReleases\Services\AssetLoader;
use EddSlReleases\Services\Shortcodes;

class AppServiceProvider implements ServiceProvider
{

    public function register(): void
    {

    }

    public function boot(): void
    {
        // Global / Frontend
        Hooks::addAction('edd_download_sl_release', DownloadReleaseFile::class);
        Hooks::addAction('wp_enqueue_scripts', AssetLoader::class, 'frontend');

        // Admin
        Hooks::addAction('admin_enqueue_scripts', AssetLoader::class, 'admin');
        Hooks::addAction('add_meta_boxes', ProductMetabox::class);
        Hooks::addAction('admin_menu', AdminPage::class, 'register');
        Hooks::addAction('edd_create_sl_release', CreateRelease::class);
        Hooks::addAction('edd_update_sl_release', UpdateRelease::class);

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
