<?php
/**
 * GitHubApi.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Services;

use EddSlReleases\Exceptions\ApiAuthorizationException;
use EddSlReleases\Exceptions\ApiException;

class GitHubApi
{
    const API_URL = 'https://api.github.com';

    /**
     * @throws ApiAuthorizationException|ApiException
     */
    public function makeRequest(string $endpoint, array $headers = []): array
    {
        $response = wp_safe_remote_get(
            $endpoint,
            [
                'timeout' => 15000,
                'headers' => wp_parse_args($headers, [
                    'Authorization' => "token {$this->getToken()}",
                    'Accept'        => 'application/vnd.github.v3+json',
                ])
            ]
        );

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message());
        }

        return $response;
    }

    /**
     * @throws ApiAuthorizationException|ApiException
     */
    public function fetchAsset(string $assetUrl): string
    {
        $response = $this->makeRequest($assetUrl, [
            'Accept' => 'application/octet-stream',
        ]);

        $validContentTypes = [
            'application/octet-stream',
            'application/zip',
        ];

        if (! in_array(wp_remote_retrieve_header($response, 'content-type'), $validContentTypes, true)) {
            throw new ApiException('Invalid content type from GitHub: '.wp_remote_retrieve_header($response, 'content-type'));
        }

        return wp_remote_retrieve_body($response);
    }

    public function getLatestRelease(string $repo): array
    {
        $response = $this->makeRequest(
            self::API_URL.'/repos/'.$repo.'/releases/latest'
        );

        if (wp_remote_retrieve_response_code($response) !== 200) {
            throw new ApiException('Unexpected response code '.wp_remote_retrieve_response_code($response));
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    public function getReleaseByTag(string $repo, string $tagName): array
    {
        $response = $this->makeRequest(
            self::API_URL.'/repos/'.$repo.'/releases/tags/'.urlencode($tagName)
        );

        if (wp_remote_retrieve_response_code($response) !== 200) {
            throw new ApiException('Unexpected response code '.wp_remote_retrieve_response_code($response));
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    /**
     * @throws ApiAuthorizationException
     */
    protected function getToken(): string
    {
        $token = defined('GITHUB_ACCESS_TOKEN') ? GITHUB_ACCESS_TOKEN : edd_get_option('gh_access_token');

        if (empty($token)) {
            throw new ApiAuthorizationException('Missing GitHub access token.');
        }

        return $token;
    }

}
