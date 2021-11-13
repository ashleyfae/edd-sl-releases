<?php
/**
 * ReleaseFileProcessor.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Services;

class ReleaseFileProcessor
{
    protected GitHubApi $gitHubApi;

    public function __construct(GitHubApi $gitHubApi)
    {
        $this->gitHubApi = $gitHubApi;
    }

    /**
     * @param  string  $remoteUrl  GitHub asset URL.
     *
     * @return string Local URL.
     * @throws \Exception
     */
    public function execute(string $remoteUrl, string $fileName): string
    {
        $directoryDetails = $this->getEddDirDetails();

        if (! file_put_contents(
            trailingslashit($directoryDetails['path']).$fileName,
            $this->gitHubApi->fetchAsset($remoteUrl)
        )) {
            throw new \Exception('Failed to save file.');
        }

        return trailingslashit($directoryDetails['url']).$fileName;
    }

    private function getEddDirDetails(): array
    {
        add_filter('upload_dir', 'edd_set_upload_dir');
        $upload_dir = wp_upload_dir();
        wp_mkdir_p($upload_dir['path']);

        return $upload_dir;
    }

}
