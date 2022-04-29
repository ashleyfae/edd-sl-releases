<?php
/**
 * CreateRelease.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Actions;

use EddSlReleases\Exceptions\ApiAuthorizationException;
use EddSlReleases\Exceptions\ApiException;
use EddSlReleases\Exceptions\FileProcessingException;
use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;
use EddSlReleases\Services\ReleaseFileProcessor;
use EddSlReleases\ValueObjects\PreparedReleaseFile;

class CreateAndPublishRelease
{
    protected bool $withEvents = true;

    public function __construct(
        protected ReleaseFileProcessor $releaseFileProcessor,
        protected ReleaseRepository $releaseRepository,
        protected SyncSoftwareLicensingReleases $productSyncer
    ) {

    }

    /**
     * Disables post-insert product syncing.
     *
     * @since 1.0
     *
     * @return $this
     */
    public function withoutEvents(): static
    {
        $this->withEvents = false;

        return $this;
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

        $preparedFile = $this->makePreparedFile($args);

        $newRelease = $this->releaseRepository->insert(
            array_merge($args, $preparedFile->toArray())
        );

        if ($this->withEvents) {
            $this->productSyncer->execute($newRelease->product_id);
        }

        return $newRelease;
    }

    /**
     * Makes a prepared release file from teh supplied arguments.
     *
     * @param  array  $args
     *
     * @return PreparedReleaseFile
     */
    protected function makePreparedFile(array $args): PreparedReleaseFile
    {
        if (! empty($args['git_asset_url'])) {
            return $this->makeFromGitAssetUrl($args);
        } elseif (! empty($_FILES['file_zip'])) {
            return $this->makeFromZipFile($args);
        } elseif (! empty($args['file_attachment_id'])) {
            return $this->makeFromAttachmentId($args);
        } elseif (! empty($args['file_path'])) {
            return new PreparedReleaseFile($args['file_path']);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'You must provide one of the following parameters: %s',
                json_encode(['git_asset_url', 'file_zip', 'file_attachment_id', 'file_path'])
            ), 400);
        }
    }

    /**
     * Makes a prepared release file from a Git URL.
     *
     * @param  array  $args
     *
     * @return PreparedReleaseFile
     * @throws FileProcessingException|ApiAuthorizationException|ApiException
     */
    protected function makeFromGitAssetUrl(array $args): PreparedReleaseFile
    {
        return $this->releaseFileProcessor->executeFromGitAsset(
            $args['git_asset_url'],
            $args['file_name']
        );
    }

    /**
     * Makes a prepared release file from a zip file.
     *
     * @param  array  $args
     *
     * @return PreparedReleaseFile
     * @throws FileProcessingException
     */
    protected function makeFromZipFile(array $args): PreparedReleaseFile
    {
        return $this->releaseFileProcessor->executeFromUploadedFile(
            'file_zip',
            $args['file_name']
        );
    }

    /**
     * Makes a prepared release file from an attachment ID.
     *
     * @param  array  $args
     *
     * @return PreparedReleaseFile
     */
    protected function makeFromAttachmentId(array $args): PreparedReleaseFile
    {
        return new PreparedReleaseFile(
            get_attached_file($args['file_attachment_id']),
            $args['file_attachment_id']
        );
    }

}
