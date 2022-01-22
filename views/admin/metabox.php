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
 */
?>
<h3><?php esc_html_e('Releases', 'edd-sl-releases'); ?></h3>
<div id="edd-sl-releases" data-product="<?php echo esc_attr($product->ID); ?>">

</div>

<button
    type="button"
    id="edd-sl-releases-trigger-new"
    class="button button-secondary"
><?php esc_html_e('Add Release', 'edd-sl-releases'); ?></button>

<div id="edd-sl-releases-new">

</div>
