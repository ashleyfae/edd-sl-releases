<?php
/**
 * metabox.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 *
 * @var WP_Post $post
 * @var EDD_SL_Download $product
 * @var string $createUrl
 */
?>
<div id="edd-sl-releases" data-product="<?php echo esc_attr($product->ID); ?>">
    <div id="edd-sl-releases-loading">
        <?php esc_html_e('Loading releases...', 'edd-sl-releases'); ?>
    </div>

    <div id="edd-sl-releases-none" class="hidden">
        <?php esc_html_e('No releases yet.', 'edd-sl-releases'); ?>
    </div>

    <div id="edd-sl-releases-list" class="hidden"></div>

    <div id="edd-sl-releases-errors" class="hidden"></div>
</div>

<a
    href="<?php echo esc_url($createUrl); ?>"
    class="button button-secondary"
><?php esc_html_e('Add Release', 'edd-sl-releases'); ?></a>
