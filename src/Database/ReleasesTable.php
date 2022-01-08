<?php
/**
 * ReleasesTable.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Database;

class ReleasesTable
{
    /**
     * @var string Database table name.
     */
    public string $tableName;

    /**
     * @var string Current table version number.
     */
    private string $version = '0.2';

    protected \wpdb $wpdb;

    protected string $versionOptionName;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->tableName         = $this->wpdb->prefix.'af_edd_sl_releases';
        $this->versionOptionName = $this->tableName.'_db_version';
    }

    private function getDbVersion(): ?string
    {
        $version = get_option($this->versionOptionName);

        return $version ? : null;
    }

    /**
     * Whether the database table requires updating (or creating).
     *
     * @return bool
     */
    public function needsUpdate(): bool
    {
        return ! $this->getDbVersion() || version_compare($this->getDbVersion(), $this->version, '<');
    }

    public function createOrUpdateTable(): void
    {
        require_once ABSPATH.'wp-admin/includes/upgrade.php';

        global $wpdb;

        dbDelta(
            "CREATE TABLE {$this->tableName} (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                product_id bigint(20) UNSIGNED NOT NULL,
                version varchar(20) NOT NULL,
                file_attachment_id bigint(20) UNSIGNED NOT NULL,
                file_path longtext NOT NULL,
                changelog longtext DEFAULT NULL,
                requirements longtext DEFAULT NULL,
                pre_release tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY product_id_created_at_pre_release (product_id, created_at, pre_release)
            ) DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate};"
        );

        update_option($this->versionOptionName, $this->version);
    }

    public function truncate(): void
    {
        $this->wpdb->query("TRUNCATE TABLE {$this->tableName}");
    }

    public function uninstall(): void
    {
        $this->wpdb->query("DROP TABLE IF EXISTS {$this->tableName}");
        delete_option($this->versionOptionName);
    }

    public function exists(): bool
    {
        $result = $this->wpdb->get_var($this->wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $this->wpdb->esc_like($this->tableName)
        ));

        return ! empty($result);
    }

}
