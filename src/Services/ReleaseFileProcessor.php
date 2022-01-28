<?php
/**
 * ReleaseFileProcessor.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Services;

use EddSlReleases\Actions\Migrations\MakeFileAttachment;
use EddSlReleases\Exceptions\ApiAuthorizationException;
use EddSlReleases\Exceptions\ApiException;
use EddSlReleases\Exceptions\FileProcessingException;
use EddSlReleases\Traits\EddUploadDir;
use EddSlReleases\ValueObjects\PreparedReleaseFile;

class ReleaseFileProcessor
{
    use EddUploadDir;

    public function __construct(protected GitHubApi $gitHubApi, protected MakeFileAttachment $attachmentMaker)
    {

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

        return new PreparedReleaseFile(
            $filePath,
            $this->attachmentMaker->createFromPath($filePath)
        );
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

        return new PreparedReleaseFile(
            $filePath,
            $this->attachmentMaker->createFromPath($filePath)
        );
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

}
