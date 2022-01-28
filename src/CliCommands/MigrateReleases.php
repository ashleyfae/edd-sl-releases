<?php
/**
 * MigrateReleases.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\CliCommands;

use EddSlReleases\Actions\Migrations\MigrateProduct;
use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;

class MigrateReleases implements CliCommand
{
    protected array $errors = [];

    public function __construct(
        protected MigrateProduct $productMigrator,
        protected ReleaseRepository $releaseRepository
    ) {

    }

    public static function commandName(): string
    {
        return 'migrate';
    }

    /**
     * Migrates releases from existing Software Licensing meta.
     *
     * ##  OPTIONS
     *
     * [--product=<product_id>]
     * : ID of the EDD product to migrate release for. If omitted, all products are migrated.
     *
     * [--dry-run]
     * : If provided, no releases are actually created.
     *
     * @param  array  $args
     * @param  array  $assocArgs
     */
    public function __invoke(array $args, array $assocArgs): void
    {
        $this->productMigrator->setProductIds(
            $this->releaseRepository->getProductIdsWithReleases()
        )
            ->dryRun(! empty($assocArgs['dry-run']));

        if (! empty($assocArgs['product'])) {
            $this->migrateProduct((int) $assocArgs['product']);
        } else {
            $this->migrateProducts();

            if (! empty($this->errors)) {
                \WP_CLI::warning(__('Failures:', 'edd-sl-releases'));
                foreach ($this->errors as $productId => $error) {
                    \WP_CLI::warning(sprintf(
                    /* Translators: %d ID of the product; %s error message */
                        __('Product %d: %s', 'edd-sl-releases'),
                        $productId,
                        $error
                    ));
                }
            }
        }
    }

    /**
     * Migrates an individual product.
     *
     * @since 1.0
     *
     * @param  int  $productId
     *
     * @return void
     */
    protected function migrateProduct(int $productId): void
    {
        $product = new \EDD_SL_Download($productId);
        try {
            if (empty($product->ID)) {
                throw new \Exception(__('Invalid product.', 'edd-sl-releases'));
            }

            \WP_CLI::line(sprintf(
            /* Translators: %d ID of the product; %s name of the product */
                __('---- Migrating product #%d (%s)', 'edd-sl-releases'),
                $product->ID,
                $product->get_name()
            ));

            $release = $this->productMigrator->execute($product);

            if ($release instanceof Release) {
                \WP_CLI::line(sprintf(
                /* Translators: %d ID of the release */
                    __('Created release #%d.', 'edd-sl-releases'),
                    $release->id
                ));
            } elseif ($this->productMigrator->dryRun) {
                \WP_CLI::line(sprintf(
                    'Stable release arguments: %s',
                    json_encode($this->productMigrator->stableArgs)
                ));

                if ($this->productMigrator->betaArgs) {
                    \WP_CLI::line(sprintf(
                        'Beta release arguments: %s',
                        json_encode($this->productMigrator->betaArgs)
                    ));
                }
            }
        } catch (\Exception $e) {
            $this->errors[$productId] = $e->getMessage();
            \WP_CLI::warning($e->getMessage());
        }
    }

    /**
     * Migrates all SL-enabled products.
     *
     * @since 1.0
     *
     * @return void
     */
    protected function migrateProducts(): void
    {
        global $wpdb;
        $productIds = $wpdb->get_col(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_edd_sl_enabled'"
        );

        \WP_CLI::line(_n(
            'Found 1 product to migrate.',
            sprintf('Found %d products to migrate.', count($productIds)),
            count($productIds),
            'edd-sl-releases'
        ));

        foreach ($productIds as $productId) {
            $this->migrateProduct((int) $productId);
            \WP_CLI::line();
        }
    }
}
