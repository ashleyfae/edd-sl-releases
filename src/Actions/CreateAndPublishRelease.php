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
    protected ?Release $latestStable = null;

    public function __construct(
        protected ReleaseFileProcessor $releaseFileProcessor,
        protected ReleaseRepository $releaseRepository,
        protected SyncSoftwareLicensingReleases $productSyncer
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

        if (! empty($args['file_url'])) {
            $preparedFile = $this->releaseFileProcessor->executeFromGitAsset(
                $args['file_url'],
                $args['file_name']
            );
        } elseif (! empty($_FILES['file_zip'])) {
            $preparedFile = $this->releaseFileProcessor->executeFromUploadedFile(
                'file_zip',
                $args['file_name']
            );
        } else {
            throw new \InvalidArgumentException('Missing file_url or file_zip argument.', 400);
        }

        $newRelease = $this->releaseRepository->insert(
            wp_parse_args($preparedFile->toArray(), $args)
        );

        $this->productSyncer->execute($newRelease->product_id);

        return $newRelease;
    }

}
