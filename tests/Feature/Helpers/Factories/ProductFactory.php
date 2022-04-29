<?php
/**
 * ProductFactory.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\Feature\Helpers\Factories;

class ProductFactory
{

    public static function make(array $args = []): \EDD_SL_Download
    {
        $args = wp_parse_args($args, [
            'post_title'                     => 'Test Download Product',
            'post_name'                      => 'test-download-product',
            'post_type'                      => 'download',
            'post_status'                    => 'publish',
            'edd_price'                      => '20.00',
            '_variable_pricing'              => 0,
            'edd_variable_prices'            => false,
            'edd_download_files'             => [
                FileFactory::make()->toArray(),
            ],
            '_edd_download_limit'            => 20,
            '_edd_hide_purchase_link'        => 1,
            'edd_product_notes'              => 'Purchase Notes',
            '_edd_product_type'              => 'default',
            '_edd_download_earnings'         => 40,
            '_edd_download_sales'            => 2,
            '_edd_download_limit_override_1' => 1,
            'edd_sku'                        => 'sku_0012',
            '_edd_sl_enabled'                => 1,
            '_edd_sl_limit'                  => 1,
            '_edd_sl_version'                => '1.0',
            '_edd_sl_changelog'              => 'Test Before More <!--more--> test After More',
            'edd_sl_download_lifetime'       => 0,
            '_edd_sl_exp_unit'               => 'years',
            '_edd_sl_exp_length'             => 1,
            '_edd_sl_keys'                   => '',
            '_edd_sl_upgrade_paths'          => [],
            '_edd_sl_upgrade_file_key'       => 0,
        ]);

        $postKeys = [
            'post_title',
            'post_name',
            'post_type',
            'post_status',
        ];
        $postArgs = array_intersect_key($args, array_flip($postKeys));

        $productId = wp_insert_post($postArgs);

        // Add meta.
        foreach ($args as $key => $value) {
            if (! in_array($key, $postKeys, true)) {
                update_post_meta($productId, $key, $value);
            }
        }

        return new \EDD_SL_Download($productId);
    }

}
