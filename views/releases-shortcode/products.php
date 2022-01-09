<?php
/**
 * Lists available products that the user has purchased.
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 *
 * @var EDD_SL_Download[] $products
 */

use EddSlReleases\ValueObjects\ReleaseDownloadUrl;

if ($products) {
    foreach ($products as $product) {
        if ($product->has_beta()) {
            $version = $product->get_beta_version();
        } else {
            $version = $product->get_version();
        }

        if (! $version) {
            continue;
        }
        ?>
        <div class="edd-sl-releases--product">
            <h2><?php echo esc_html($product->get_name()); ?></h2>

            <div class="edd-sl-releases--product-actions">
                <a href="<?php echo esc_url(new ReleaseDownloadUrl($product->ID, $version)); ?>">
                    <?php echo esc_html(sprintf(__('Download version %s', 'edd-sl-releases'), $version)); ?>
                </a>

                <a href="#">
                    <?php esc_html_e('Previous releases', 'edd-sl-releases'); ?>
                </a>
            </div>
        </div>
        <?php
    }
} else {

}
