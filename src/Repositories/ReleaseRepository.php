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

    /**
     * @param  int  $productId
     *
     * @return Release
     * @throws ModelNotFoundException
     */
    public function getLatestStableRelease(int $productId): Release
    {
        $result = wp_cache_get('latest_stable_release', 'af_edd_sl_releases');
        if (is_array($result)) {
            return new Release($result);
        }

        $result = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->releasesTable->tableName}
                WHERE product_id = %d
                AND pre_release = 0
                ORDER BY created_at DESC",
                $productId
            ),
            \ARRAY_A
        );

        wp_cache_set('latest_stable_release', $result, 'af_edd_sl_releases');

        if ($result) {
            return new Release($result);
        }

        throw new ModelNotFoundException();
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
            'product_id'   => null,
            'version'      => null,
            'file_url'     => null,
            'changelog'    => null,
            'requirements' => null,
            'pre_release'  => 0,
            'created_at'   => gmdate('Y-m-d H:i:s'),
        ]);

        $formats = [
            'product_id'   => '%d',
            'version'      => '%s',
            'file_url'     => '%s',
            'changelog'    => '%s',
            'requirements' => '%s',
            'pre_release'  => '%d',
            'created_at'   => '%s',
        ];

        // Check for any missing values.
        $required = ['product_id', 'version', 'file_url'];
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

        return $this->getById($releaseId);
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

}
