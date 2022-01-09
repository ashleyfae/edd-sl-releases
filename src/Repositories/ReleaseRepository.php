<?php
/**
 * ReleaseRepository.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Repositories;

use EddSlReleases\Database\ReleasesTable;
use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\Models\Release;

class ReleaseRepository
{
    private ReleasesTable $releasesTable;
    private \wpdb $wpdb;

    public function __construct(ReleasesTable $releasesTable)
    {
        global $wpdb;

        $this->releasesTable = $releasesTable;
        $this->wpdb          = $wpdb;
    }

    public function getLatest(int $productId, bool $preRelease = false): Release
    {
        $cacheKey = $preRelease ? 'latest_pre_release_' : 'latest_stable_release_';
        $cacheKey .= $productId;
        $result   = wp_cache_get($cacheKey, 'af_edd_sl_releases');
        if (is_array($result)) {
            return new Release($result);
        }

        $result = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->releasesTable->tableName}
                WHERE product_id = %d
                AND pre_release = %d
                ORDER BY created_at DESC",
                $productId,
                (int) $preRelease
            ),
            \ARRAY_A
        );

        wp_cache_set($cacheKey, $result, 'af_edd_sl_releases');

        if ($result) {
            return new Release($result);
        }

        throw new ModelNotFoundException();
    }

    /**
     * @param  int  $productId
     *
     * @return Release
     * @throws ModelNotFoundException
     */
    public function getLatestStableRelease(int $productId): Release
    {
        return $this->getLatest($productId);
    }

    public function getLatestPreRelease(int $productId): Release
    {
        return $this->getLatest($productId, true);
    }

    /**
     * @param  array  $data
     *
     * @return Release
     * @throws ModelNotFoundException|\Exception
     */
    public function insert(array $data): Release
    {
        $data = wp_parse_args($data, [
            'product_id'         => null,
            'version'            => null,
            'file_attachment_id' => null,
            'file_path'          => null,
            'file_name'          => null,
            'changelog'          => null,
            'requirements'       => null,
            'pre_release'        => 0,
            'created_at'         => gmdate('Y-m-d H:i:s'),
        ]);

        $formats = [
            'product_id'         => '%d',
            'version'            => '%s',
            'file_attachment_id' => '%d',
            'file_path'          => '%s',
            'file_name'          => '%s',
            'changelog'          => '%s',
            'requirements'       => '%s',
            'pre_release'        => '%d',
            'created_at'         => '%s',
        ];

        // Check for any missing values.
        $required = ['product_id', 'version', 'file_attachment_id', 'file_path'];
        foreach ($required as $col) {
            if (empty($data[$col])) {
                throw new \Exception("Missing required value: {$col}.");
            }
        }

        if (isset($data['requirements'])) {
            $data['requirements'] = ! empty($data['requirements']) && is_array($data['requirements'])
                ? json_encode($data['requirements'])
                : null;
        }

        // Only allowed columns, please.
        $data = array_intersect_key($data, $formats);

        // Reorder $formats to match the order of columns in $data.
        $formats = array_replace(array_flip(array_keys($data)), $formats);

        if (! $this->wpdb->insert($this->releasesTable->tableName, $data, $formats)) {
            throw new \Exception('Failed to create release.');
        }

        $releaseId = $this->wpdb->insert_id;
        if (empty($releaseId)) {
            throw new \Exception('Failed to create release.');
        }

        $release = $this->getById($releaseId);

        if ($release->pre_release) {
            wp_cache_delete('latest_pre_release_'.$release->product_id);
        } else {
            wp_cache_delete('latest_stable_release_'.$release->product_id);
        }

        /**
         * Triggers when a new release is created.
         *
         * @since 1.0
         *
         * @param  Release  $release
         */
        do_action('edd-sl-releases/release/created', $release);

        return $release;
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getById(int $releaseId): Release
    {
        $row = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->releasesTable->tableName} WHERE id = %d",
            $releaseId
        ), ARRAY_A);

        if (empty($row)) {
            throw new ModelNotFoundException();
        }

        return new Release($row);
    }

    /**
     * Lists the releases for a given product.
     *
     * @param  int  $productId
     * @param  bool  $preReleases
     * @param  int  $offset
     *
     * @return Release[]
     */
    public function listForProduct(int $productId, bool $preReleases = false, int $offset = 0): array
    {
        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->releasesTable->tableName}
WHERE product_id = %d AND pre_release = %d
ORDER BY created_at DESC
LIMIT {$offset}, 10",
            $productId,
            (int) $preReleases
        ), ARRAY_A);

        if (empty($results)) {
            return [];
        }

        foreach ($results as $key => $row) {
            $results[$key] = new Release($row);
        }

        return $results;
    }

}
