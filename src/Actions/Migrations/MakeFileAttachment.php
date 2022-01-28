<?php
/**
 * MakeFileAttachment.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Actions\Migrations;

use EddSlReleases\Exceptions\FileProcessingException;
use EddSlReleases\Traits\EddUploadDir;

class MakeFileAttachment
{
    use EddUploadDir;

    /**
     * @throws FileProcessingException
     */
    public function createFromUrl($file): int
    {
        if (empty($file['file'])) {
            throw new FileProcessingException(sprintf('No file URL in file: %s', json_encode($file)));
        }

        return $this->createFromPath(
            $this->urlToPath($file['file'])
        );
    }

    /**
     * @throws FileProcessingException
     */
    protected function urlToPath(string $url): string
    {
        $path = str_replace(
            trailingslashit(site_url()),
            trailingslashit(ABSPATH),
            $url
        );

        if (! file_exists($path)) {
            throw new FileProcessingException(sprintf(
                'File %s does not exist.',
                $path
            ));
        }

        return $path;
    }

    /**
     * @throws FileProcessingException
     */
    public function createFromPath(string $filePath): int
    {
        $attachmentId = wp_insert_attachment(
            [
                'guid'           => trailingslashit($this->getEddDirDetails()['url']).basename($filePath),
                'post_mime_type' => wp_check_filetype(basename($filePath))['type'] ?? '',
                'post_title'     => trim(preg_replace('/[^a-z0-9.\-_]/i', '', basename($filePath))),
                'post_status'    => 'inherit'
            ],
            $filePath,
            0,
            true
        );

        if (is_wp_error($attachmentId)) {
            throw new FileProcessingException(sprintf(
                'Attachment creation failure: %s',
                $attachmentId->get_error_message()
            ));
        }

        return (int) $attachmentId;
    }

}
