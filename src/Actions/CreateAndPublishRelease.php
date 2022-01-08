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

    public function __construct(
        protected ReleaseFileProcessor $releaseFileProcessor,
        protected ReleaseRepository $releaseRepository
    ) {

    }

    /**
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

        $release = $this->releaseRepository->insert(
            wp_parse_args($preparedFile->toArray(), $args)
        );

        $this->updateProductDownloads($release);

        return $release;
    }

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
