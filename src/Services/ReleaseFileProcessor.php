<?php
/**
 * ReleaseFileProcessor.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Services;

use EddSlReleases\Exceptions\ApiAuthorizationException;
use EddSlReleases\Exceptions\ApiException;
use EddSlReleases\Exceptions\FileProcessingException;
use EddSlReleases\ValueObjects\PreparedReleaseFile;

class ReleaseFileProcessor
{
    protected GitHubApi $gitHubApi;

    public function __construct(GitHubApi $gitHubApi)
    {
        $this->gitHubApi = $gitHubApi;
    }

    /**
     * @param  string  $remoteUrl  GitHub asset URL.
     * @param  string  $fileName  Desired file name.
     *
     * @return PreparedReleaseFile
     * @throws FileProcessingException|ApiException|ApiAuthorizationException
     */
    public function executeFromGitAsset(string $remoteUrl, string $fileName): PreparedReleaseFile
    {
        $filePath = $this->saveFileToEddDir(
            $this->gitHubApi->fetchAsset($remoteUrl),
            $fileName
        );

        return new PreparedReleaseFile($filePath, $this->createAttachment($filePath));
    }

    /**
     * @param  string  $fileKey
     * @param  string  $fileName
     *
     * @return PreparedReleaseFile
     * @throws FileProcessingException
     */
    public function executeFromUploadedFile(string $fileKey, string $fileName): PreparedReleaseFile
    {
        if (! is_array($_FILES[$fileKey] ?? null)) {
            throw new FileProcessingException('File key does not exist.');
        }

        // Check if we have errors.
        if (($_FILES[$fileKey]['error'] ?? null) !== UPLOAD_ERR_OK) {
            throw new FileProcessingException('File error: '.($_FILES[$fileKey]['error'] ?? null));
        }

        $filePath = $this->makeFilePathFromName($fileName);

        $success = move_uploaded_file(
            $_FILES[$fileKey]['tmp_name'],
            $filePath
        );

        if (! $success) {
            throw new FileProcessingException('Failed to move file.');
        }

        return new PreparedReleaseFile($filePath, $this->createAttachment($filePath));
    }

    /**
     * @throws FileProcessingException
     */
    protected function createAttachment(string $filePath): int
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
            throw new FileProcessingException(sprintf('Attachment creation failure: %s', $attachmentId->get_error_message()));
        }

        return (int) $attachmentId;
    }

    /**
     * @throws FileProcessingException
     */
    public function saveFileToEddDir(mixed $contents, string $fileName): string
    {
        $filePath = $this->makeFilePathFromName($fileName);

        if (! file_put_contents(
            $filePath,
            $contents
        )) {
            throw new FileProcessingException('Failed to save file.');
        }

        return $filePath;
    }

    protected function makeFilePathFromName(string $fileName): string
    {
        $directoryDetails = $this->getEddDirDetails();

        return trailingslashit($directoryDetails['path']).time().'-'.$fileName;
    }

    private function getEddDirDetails(): array
    {
        add_filter('upload_dir', 'edd_set_upload_dir');
        $upload_dir = wp_upload_dir();
        wp_mkdir_p($upload_dir['path']);

        return $upload_dir;
    }

}
