<?php
/**
 * SyncSoftwareLicensingReleases.php
 *
 * Updates the product's latest stable and latest pre-releases.
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Actions;

use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;

class SyncSoftwareLicensingReleases
{
    public ?Release $latestStable = null;
    public ?Release $latestPreRelease = null;

    public function __construct(protected ReleaseRepository $releaseRepository)
    {

    }

    public function execute(int $productId): void
    {
        edd_debug_log(sprintf('Updating SL release data for product #%d.', $productId));

        $product = new \EDD_SL_Download($productId);

        try {
            $this->latestStable = $this->releaseRepository->getLatestStableRelease($productId);

            $this->updateSlVersion($this->latestStable);
            $this->updateProductDownloads($this->latestStable, $product);
        } catch (ModelNotFoundException $e) {
            edd_debug_log('No latest stable release found.');
        }

        try {
            $this->latestPreRelease = $this->releaseRepository->getLatestPreRelease($productId);

            $this->updateSlVersion($this->latestPreRelease);
            $this->updateProductDownloads($this->latestPreRelease, $product);
        } catch (ModelNotFoundException $e) {
            edd_debug_log('No latest pre release found.');
        }
    }

    /**
     * Updates the Software Licensing version information for both the latest stable
     * and latest pre-release.
     *
     * @param  Release  $release
     *
     * @return void
     */
    protected function updateSlVersion(Release $release): void
    {
        /*
         * If this is a pre-release and the latest stable is a higher version,
         * then disable the beta release.
         */
        if (
            $release->pre_release &&
            $this->latestStable instanceof Release &&
            version_compare($this->latestStable->version, $release->version, '>')
        ) {
            delete_post_meta($release->product_id, '_edd_sl_beta_enabled');
            return;
        }

        $prefix       = $release->pre_release ? '_beta' : '';
        $enabledKey   = "_edd_sl{$prefix}_enabled";
        $versionKey   = "_edd_sl{$prefix}_version";
        $fileKey      = "_edd_sl{$prefix}_upgrade_file_key";
        $changelogKey = "_edd_sl{$prefix}_changelog";

        update_post_meta($release->product_id, $enabledKey, true);
        update_post_meta($release->product_id, $versionKey, $release->version);
        update_post_meta($release->product_id, $fileKey, 0);

        if ($release->changelog) {
            update_post_meta($release->product_id, $changelogKey, $release->changelog);
        }

        if (! $release->pre_release && $release->requirements) {
            update_post_meta($release->product_id, '_edd_sl_required_versions', $release->requirements);
        } else {
            //@todo Not sure if we should delete.
            //delete_post_meta($release->product_id, '_edd_sl_required_versions');
        }
    }

    /**
     * Updates the product's download file.
     *
     * @param  Release  $release
     * @param  \EDD_SL_Download  $product
     *
     * @return void
     */
    protected function updateProductDownloads(Release $release, \EDD_SL_Download $product): void
    {
        $metaKey       = $release->pre_release ? '_edd_sl_beta_files' : 'edd_download_files';
        $existingFiles = $release->pre_release ? $product->get_beta_files() : $product->get_files();
        $fileIndex     = $release->pre_release ? $product->get_beta_upgrade_file_key() : $product->get_upgrade_file_key();

        if ($fileIndex === false || ! array_key_exists($fileIndex, $existingFiles)) {
            $fileIndex = count($existingFiles) ? max(array_keys($existingFiles)) + 1 : 0;
        }

        $existingFiles[$fileIndex] = [
            [
                'index'         => $fileIndex,
                'name'          => $release->file_name,
                'file'          => $release->getProtectedFileUrl(),
                'condition'     => 'all',
                'attachment_id' => $release->file_attachment_id,
            ]
        ];

        update_post_meta($release->product_id, $metaKey, $existingFiles);
    }

}
