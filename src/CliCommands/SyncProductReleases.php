<?php
/**
 * SyncProductReleases.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\CliCommands;

use EddSlReleases\Actions\SyncSoftwareLicensingReleases;

class SyncProductReleases implements CliCommand
{

    public function __construct(protected SyncSoftwareLicensingReleases $productSyncer)
    {

    }

    public static function commandName(): string
    {
        return 'sync';
    }

    /**
     * Publishes a new release.
     *
     * ##  OPTIONS
     *
     * <product_id>
     * : ID of the EDD product sync.
     *
     * @param  array  $assocArgs
     * @param  array  $args
     */
    public function __invoke(array $assocArgs, array $args): void
    {
        $product = new \EDD_SL_Download($assocArgs[0] ?? 0);
        if (! $product->ID) {
            \WP_CLI::error(__('Invalid product.', 'edd-sl-releases'));
        }

        /* Translators: %s name of the product */
        \WP_CLI::line(sprintf(__('Syncing product %s...', 'edd-sl-releases'), $product->get_name()));

        $this->productSyncer->execute($product->ID);

        \WP_CLI::success(sprintf(
            /* Translators: $%1$s stable version; %2$s beta version */
            __('Sync complete. Latest stable: %1$s; Latest beta: %2$s.', 'edd-sl-releases'),
            $this->productSyncer->latestStable->version ?? __('n/a', 'edd-sl-releases'),
            $this->productSyncer->latestPreRelease->version ?? __('n/a', 'edd-sl-releases'),
        ));
    }
}
