<?php
/**
 * ApplicationServiceProvider.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ServiceProviders;

use EddSlReleases\Helpers\Hooks;
use EddSlReleases\Helpers\ReleaseMapper;

class ApplicationServiceProvider implements ServiceProvider
{

    public function register(): void
    {

    }

    public function boot(): void
    {
        Hooks::addFilter('edd_sl_download_version', ReleaseMapper::class, 'mapVersion', 10, 2);
        Hooks::addFilter('edd_sl_download_changelog', ReleaseMapper::class, 'mapChangelog', 10, 2);
        Hooks::addFilter('get_download_metadata', ReleaseMapper::class, 'mapRequirements', 10, 5);
    }
}
