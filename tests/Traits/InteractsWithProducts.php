<?php
/**
 * InteractsWithProducts.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\Traits;

use EddSlReleases\Tests\Feature\Helpers\Factories\ProductFactory;
use EddSlReleases\Tests\Feature\Helpers\File;

trait InteractsWithProducts
{

    protected function createProduct(): \EDD_SL_Download
    {
        return ProductFactory::make();
    }

    /**
     * Sets the version number for the product.
     *
     * @param  int  $downloadId
     * @param  string  $version
     */
    protected function setProductVersion(int $downloadId, string $version): void
    {
        update_post_meta($downloadId, '_edd_sl_version', $version);
    }

    /**
     * Sets the beta version for the product.
     *
     * @param  int  $downloadId
     * @param  bool|string  $betaVersion
     */
    protected function setProductBeta(int $downloadId, bool|string $betaVersion): void
    {
        if (false === $betaVersion) {
            delete_post_meta($downloadId, '_edd_sl_beta_enabled');
            delete_post_meta($downloadId, '_edd_sl_beta_version');
        } else {
            update_post_meta($downloadId, '_edd_sl_beta_enabled', true);
            update_post_meta($downloadId, '_edd_sl_beta_version', $betaVersion);
        }
    }

    protected function setBetaChangelog(int $downloadId, string $changelog): void
    {
        update_post_meta($downloadId, '_edd_sl_beta_changelog', $changelog);
    }

    protected function setBetaFile(int $downloadId, File $file): void
    {
        update_post_meta($downloadId, '_edd_sl_beta_files', [
            $file->toArray(),
        ]);

        update_post_meta($downloadId, '_edd_sl_beta_upgrade_file_key', 0);
    }

    /**
     * Sets the requirements for the product.
     *
     * @param  int  $downloadId
     * @param  array  $requirements
     */
    protected function setProductRequirements(int $downloadId, array $requirements = [])
    {
        if (empty($requirements)) {
            delete_post_meta($downloadId, '_edd_sl_required_versions');
        } else {
            update_post_meta($downloadId, '_edd_sl_required_versions', $requirements);
        }
    }

}
