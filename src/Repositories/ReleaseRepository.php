<?php
/**
 * ReleaseRepository.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
 */

namespace EddSlReleases\Repositories;

use EddSlReleases\Database\ReleasesTable;
use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\Models\Release;

class ReleaseRepository
{
    protected ReleasesTable $releasesTable;
    protected \wpdb $wpdb;

    public function __construct(ReleasesTable $releasesTable)
    {
        global $wpdb;

        $this->releasesTable = $releasesTable;
        $this->wpdb          = $wpdb;
    }

    /**
     * Retrieves the latest stable or pre-release for a given product.
     *
     * @since 1.0
     *
     * @param  int  $productId
     * @param  bool  $preRelease
     *
     * @return Release
     * @throws ModelNotFoundException
     */
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
                ORDER BY released_at DESC",
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
     * Returns the latest stable release.
     *
     * @since 1.0
     *
     * @param  int  $productId
     *
     * @return Release
     * @throws ModelNotFoundException
     */
    public function getLatestStableRelease(int $productId): Release
    {
        return $this->getLatest($productId);
    }

    /**
     * Returns the latest pre-release.
     *
     * @since 1.0
     *
     * @param  int  $productId
     *
     * @return Release
     * @throws ModelNotFoundException
     */
    public function getLatestPreRelease(int $productId): Release
    {
        return $this->getLatest($productId, true);
    }

    /**
     * Inserts a new release.
     *
     * @since 1.0
     *
     * @param  array  $data
     *
     * @return Release
     * @throws ModelNotFoundException|\Exception
     */
    public function insert(array $data): Release
    {
        $data = array_merge([
            'product_id'         => null,
            'version'            => null,
            'file_attachment_id' => null,
            'file_path'          => null,
            'file_name'          => null,
            'changelog'          => null,
            'requirements'       => null,
            'pre_release'        => 0,
            'released_at'        => gmdate('Y-m-d H:i:s'),
        ], $data);

        $formats = [
            'product_id'         => '%d',
            'version'            => '%s',
            'file_attachment_id' => '%d',
            'file_path'          => '%s',
            'file_name'          => '%s',
            'changelog'          => '%s',
            'requirements'       => '%s',
            'pre_release'        => '%d',
            'released_at'        => '%s',
        ];

        // Check for any missing values.
        $required = ['product_id', 'version', 'file_attachment_id', 'file_path', 'file_name'];
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
     * Updates an existing release.
     *
     * @since 1.0
     *
     * @param  Release  $release
     *
     * @return void
     */
    public function update(Release $release): void
    {
        $this->wpdb->update(
            $this->releasesTable->tableName,
            [
                'version'            => $release->version,
                'file_attachment_id' => $release->file_attachment_id,
                'file_path'          => $release->file_path,
                'file_name'          => $release->file_name,
                'changelog'          => $release->changelog,
                'requirements'       => $release->requirements ? json_encode($release->requirements) : null,
                'pre_release'        => (int) $release->pre_release,
                'released_at'        => $release->released_at,
            ],
            [
                'id' => $release->id,
            ],
            [
                '%s', // version
                '%d', // file_attachment_id
                '%s', // file_path
                '%s', // file_name
                '%s', // changelog
                '%s', // requirements
                '%d', // pre_release
                '%s', // released_at
            ],
            [
                '%d'
            ]
        );
    }

    /**
     * Retrieves a single release by its ID.
     *
     * @since 1.0
     *
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
     * @since 1.0
     *
     * @param  int  $productId
     * @param  bool|null  $preReleases
     * @param  int  $perPage
     * @param  int  $offset
     *
     * @return Release[]
     */
    public function listForProduct(int $productId, ?bool $preReleases = null, int $perPage = 10, int $offset = 0): array
    {
        $preReleasesSql = '';
        if (! is_null($preReleases)) {
            $preReleasesSql = $this->wpdb->prepare("AND pre_release = %d", $preReleases);
        }

        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->releasesTable->tableName}
                WHERE product_id = %d
                {$preReleasesSql}
                ORDER BY released_at DESC
                LIMIT %d, %d",
            $productId,
            $offset,
            $perPage
        ), ARRAY_A);

        if (empty($results)) {
            return [];
        }

        foreach ($results as $key => $row) {
            $results[$key] = new Release($row);
        }

        return $results;
    }

    /**
     * Counts the number of releases a product has.
     *
     * @since 1.0
     *
     * @param  int  $productId
     * @param  bool|null  $preReleases
     *
     * @return int
     */
    public function countForProduct(int $productId, ?bool $preReleases = null): int
    {
        $preReleasesSql = '';
        if (! is_null($preReleases)) {
            $preReleasesSql = $this->wpdb->prepare("AND pre_release = %d", $preReleases);
        }

        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->releasesTable->tableName} WHERE product_id = %d {$preReleasesSql}",
            $productId
        ));

        return (int) $count;
    }

    /**
     * Returns a Release for a given product ID + version combination.
     *
     * @since 1.0
     *
     * @param  int  $productId
     * @param  string  $version
     *
     * @return Release
     * @throws ModelNotFoundException
     */
    public function getByProductVersion(int $productId, string $version): Release
    {
        $release = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$this->releasesTable->tableName}
            WHERE product_id = %d
            AND version = %s",
            $productId,
            $version
        ), ARRAY_A);

        if (empty($release)) {
            throw new ModelNotFoundException();
        }

        return new Release($release);
    }

    /**
     * Returns the IDs of all products that have releases.
     *
     * @since 1.0
     *
     * @return int[]
     */
    public function getProductIdsWithReleases(): array
    {
        $ids = $this->wpdb->get_col(
            "SELECT DISTINCT product_id FROM {$this->releasesTable->tableName}"
        );

        return array_map('intval', $ids);
    }

}
