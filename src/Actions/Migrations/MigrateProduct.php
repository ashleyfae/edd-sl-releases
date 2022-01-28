<?php
/**
 * MigrateProduct.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Actions\Migrations;

use EddSlReleases\Actions\CreateAndPublishRelease;
use EddSlReleases\Exceptions\FileProcessingException;
use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\Models\Release;

class MigrateProduct
{
    protected array $productIdsWithReleases = [];
    public bool $dryRun = false;
    public array $stableArgs = [];
    public array $betaArgs = [];
    protected \EDD_SL_Download $product;

    public function __construct(
        protected CreateAndPublishRelease $releaseCreator,
        protected MakeFileAttachment $attachmentMaker
    ) {

    }

    /**
     * Sets an array of product IDs that already have releases.
     * These will not be migrated to ensure we don't duplicate stuff.
     *
     * @since 1.0
     *
     * @param  array  $ids
     *
     * @return $this
     */
    public function setProductIds(array $ids): static
    {
        $this->productIdsWithReleases = $ids;

        return $this;
    }

    /**
     * Sets the dry run property. If true, no Releases are actually created.
     *
     * @since 1.0
     *
     * @param  bool  $isDryRun
     *
     * @return $this
     */
    public function dryRun(bool $isDryRun): static
    {
        $this->dryRun = $isDryRun;

        return $this;
    }

    /**
     * Migrates the product's stable and beta (if applicable) data to Releases.
     *
     * @since 1.0
     *
     * @throws ModelNotFoundException|FileProcessingException|\Exception
     */
    public function execute(\EDD_SL_Download $product): ?Release
    {
        $this->product    = $product;
        $this->stableArgs = [];
        $this->betaArgs   = [];
        $file             = $this->product->get_files()[$this->product->get_upgrade_file_key()] ?? null;

        $this->validateMigration($this->product, $file);

        $stableRelease = null;

        $this->stableArgs = [
            'product_id'         => $this->product->ID,
            'version'            => $this->product->get_version(),
            'file_attachment_id' => $this->getOrMakeFileAttachmentId($file),
            'file_name'          => $file['name'] ?? null,
            'changelog'          => $this->product->get_changelog(),
            'requirements'       => $this->product->get_requirements(),
            'released_at'        => $this->product->post_modified_gmt,
        ];

        if (! $this->dryRun) {
            $stableRelease = $this->releaseCreator
                ->withoutEvents()
                ->execute($this->stableArgs);
        }

        $this->migrateBeta($this->product);

        return $stableRelease;
    }

    /**
     * Validates whether we should actually perform a migration. Exception is thrown
     * if we should not.
     *
     * @since 1.0
     *
     * @param  \EDD_SL_Download  $product
     * @param  array|null        $file
     *
     * @return void
     * @throws \Exception
     */
    protected function validateMigration(\EDD_SL_Download $product, ?array $file): void
    {
        if (in_array($product->ID, $this->productIdsWithReleases)) {
            throw new \Exception(sprintf(
            /* Translators: %d ID of the product */
                __('Product #%d already has releases.', 'edd-sl-releases'),
                $product->ID
            ));
        }

        if (! $product->licensing_enabled()) {
            throw new \Exception(sprintf(
                __('Licensing is not enabled for product #%d.', 'edd-sl-releases'),
                $product->ID
            ));
        }

        if (is_null($file)) {
            throw new \Exception(sprintf(
            /* Translators: %s file key; %d ID of the product */
                __('File key %s not found in files array for product #%d.', 'edd-sl-releases'),
                $product->get_upgrade_file_key(),
                $product->ID
            ));
        }
    }

    /**
     * Migrates the beta release if there is one.
     *
     * @since 1.0
     *
     * @throws ModelNotFoundException|FileProcessingException
     */
    public function migrateBeta(\EDD_SL_Download $product): ?Release
    {
        if ($product->has_beta() && ($betaFile = $product->get_beta_files()[$product->get_beta_upgrade_file_key()] ?? null)) {
            $this->betaArgs = [
                'product_id'         => $product->ID,
                'version'            => $product->get_beta_version(),
                'file_attachment_id' => $this->getOrMakeFileAttachmentId($betaFile, true),
                'file_name'          => $betaFile['name'] ?? null,
                'changelog'          => $product->get_beta_changelog(),
                'requirements'       => $product->get_requirements(),
                'pre_release'        => true,
                'released_at'        => $product->post_modified_gmt,
            ];

            if (! $this->dryRun) {
                return $this->releaseCreator
                    ->withoutEvents()
                    ->execute($this->betaArgs);
            }
        }

        return null;
    }

    /**
     * @throws FileProcessingException
     */
    protected function getOrMakeFileAttachmentId(array $file, bool $preRelease = false): int
    {
        if (! empty($file['attachment_id'])) {
            return (int) $file['attachment_id'];
        }

        if ($this->dryRun) {
            throw new \Exception(
                __('No attachment ID in file. (Will attempt to create one in live run.)', 'edd-sl-releases')
            );
        }

        $attachmentId = $this->attachmentMaker->createFromUrl($file);

        $file['attachment_id'] = $attachmentId;

        $this->updateFiles($file, $preRelease);

        return $attachmentId;
    }

    /**
     * Updates the attachment ID associated with a product.
     * This is run if we ended up creating an attachment, so we don't continue to create them over
     * and over with each run of the migration.
     *
     * @since 1.0
     *
     * @param  array  $file
     * @param  bool  $preRelease
     *
     * @return void
     */
    protected function updateFiles(array $file, bool $preRelease = false): void
    {
        $files   = $preRelease ? $this->product->get_beta_files() : $this->product->get_files();
        $fileKey = $preRelease ? $this->product->get_beta_upgrade_file_key() : $this->product->get_upgrade_file_key();
        $metaKey = $preRelease ? '_edd_sl_beta_files' : 'edd_download_files';

        $files[$fileKey] = $file;

        update_post_meta($this->product->ID, $metaKey, $files);
    }

}
