<?php
/**
 * InteractsWithProducts.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\Traits;

trait InteractsWithProducts
{

    protected function createProduct(): \EDD_SL_Download
    {
        $productId = wp_insert_post([
            'post_title'  => 'Test Download Product',
            'post_name'   => 'test-download-product',
            'post_type'   => 'download',
            'post_status' => 'publish',
        ]);

        $meta = [
            'edd_price'                      => '20.00',
            '_variable_pricing'              => 0,
            'edd_variable_prices'            => false,
            'edd_download_files'             => [
                [
                    'name'      => 'Simple File 1',
                    'file'      => 'http://localhost/simple-file1.jpg',
                    'condition' => 0,
                ]
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
            '_edd_sl_version'                => 1,
            '_edd_sl_changelog'              => 'Test Before More <!--more--> test After More',
            'edd_sl_download_lifetime'       => 0,
            '_edd_sl_exp_unit'               => 'years',
            '_edd_sl_exp_length'             => 1,
            '_edd_sl_keys'                   => '',
            '_edd_sl_upgrade_paths'          => array(),
        ];

        foreach ($meta as $key => $value) {
            update_post_meta($productId, $key, $value);
        }

        return new \EDD_SL_Download($productId);
    }

    /**
     * Sets the version number for the product.
     *
     * @param  int  $downloadId
     * @param  string  $version
     */
    protected function setProductVersion(int $downloadId, string $version): void
    {
        update_post_meta($downloadId, '_edd_sl_version', $version);
    }

    /**
     * Sets the beta version for the product.
     *
     * @param  int  $downloadId
     * @param  string|false  $betaVersion
     */
    protected function setProductBeta(int $downloadId, $betaVersion): void
    {
        if (false === $betaVersion) {
            delete_post_meta($downloadId, '_edd_sl_beta_enabled');
            delete_post_meta($downloadId, '_edd_sl_beta_version');
        } else {
            update_post_meta($downloadId, '_edd_sl_beta_enabled', true);
            update_post_meta($downloadId, '_edd_sl_beta_version', $betaVersion);
        }
    }

    /**
     * Sets the requirements for the product.
     *
     * @param  int  $downloadId
     * @param  array  $requirements
     */
    protected function setProductRequirements(int $downloadId, array $requirements = [])
    {
        if (empty($requirements)) {
            delete_post_meta($downloadId, '_edd_sl_required_versions');
        } else {
            update_post_meta($downloadId, '_edd_sl_required_versions', $requirements);
        }
    }

}
