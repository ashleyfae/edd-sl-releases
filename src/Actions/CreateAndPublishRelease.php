<?php
/**
 * CreateRelease.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Actions;

use EddSlReleases\Exceptions\FileProcessingException;
use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;
use EddSlReleases\Services\ReleaseFileProcessor;

class CreateAndPublishRelease
{
    protected string $fileName;
    protected ?Release $latestStable = null;

    public function __construct(
        protected ReleaseFileProcessor $releaseFileProcessor,
        protected ReleaseRepository $releaseRepository
    ) {

    }

    /**
     * Saves the release asset, creates a WordPress attachment for it, inserts the release into
     * the database, and updates the Software Licensing data to use the new release.
     *
     * @param  array  $args
     *
     * @return Release
     * @throws ModelNotFoundException|FileProcessingException|\InvalidArgumentException
     */
    public function execute(array $args): Release
    {
        if (empty($args['file_name'])) {
            throw new \InvalidArgumentException('Missing required file_name argument.', 400);
        }

        $this->fileName = $args['file_name'];

        if (! empty($args['file_url'])) {
            $preparedFile = $this->releaseFileProcessor->executeFromGitAsset(
                $args['file_url'],
                $this->fileName
            );
        } elseif (! empty($_FILES['file_zip'])) {
            $preparedFile = $this->releaseFileProcessor->executeFromUploadedFile(
                'file_zip',
                $this->fileName
            );
        } else {
            throw new \InvalidArgumentException('Missing file_url or file_zip argument.', 400);
        }

        $newRelease = $this->releaseRepository->insert(
            wp_parse_args($preparedFile->toArray(), $args)
        );

        $this->updateProductReleases($newRelease->product_id);

        return $newRelease;
    }

    /**
     * Updates the product's latest stable and latest pre-releases.
     *
     * @param  int  $productId
     *
     * @return void
     */
    protected function updateProductReleases(int $productId): void
    {
        edd_debug_log(sprintf('Updating SL release data for product #%d.', $productId));

        try {
            $this->latestStable = $this->releaseRepository->getLatestStableRelease($productId);

            $this->updateSlVersion($this->latestStable);
            $this->updateProductDownloads($this->latestStable);
        } catch (ModelNotFoundException $e) {
            edd_debug_log('No latest stable release found.');
        }

        try {
            $latestPreRelease = $this->releaseRepository->getLatestPreRelease($productId);

            $this->updateSlVersion($latestPreRelease);
            $this->updateProductDownloads($latestPreRelease);
        } catch (ModelNotFoundException $e) {

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

        if ($release->pre_release) {
            $enabledKey   = '_edd_sl_beta_enabled';
            $versionKey   = '_edd_sl_beta_version';
            $fileKey      = '_edd_sl_beta_upgrade_file_key';
            $changelogKey = '_edd_sl_beta_changelog';
        } else {
            $enabledKey   = '_edd_sl_enabled';
            $versionKey   = '_edd_sl_version';
            $fileKey      = '_edd_sl_upgrade_file_key';
            $changelogKey = '_edd_sl_changelog';
        }

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
     *
     * @return void
     */
    protected function updateProductDownloads(Release $release): void
    {
        $metaKey = $release->pre_release ? '_edd_sl_beta_files' : 'edd_download_files';

        update_post_meta($release->product_id, $metaKey, [
            [
                'index'         => 0,
                'name'          => $this->fileName,
                'file'          => wp_get_attachment_url($release->file_attachment_id),
                'condition'     => 'all',
                'attachment_id' => $release->file_attachment_id,
            ]
        ]);
    }

}
