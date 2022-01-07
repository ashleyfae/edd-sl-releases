<?php
/**
 * DatabaseServiceProvider.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ServiceProviders;

use EddSlReleases\Database\ReleasesTable;

class DatabaseServiceProvider implements ServiceProvider
{

    public function register(): void
    {

    }

    public function boot(): void
    {
        if (eddSlReleases(ReleasesTable::class)->needsUpdate()) {
            eddSlReleases(ReleasesTable::class)->createOrUpdateTable();
        }
    }
}
