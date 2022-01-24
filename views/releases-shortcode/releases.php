<?php
/**
 * Lists releases for a single product.
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 *
 * @var \EddSlReleases\Models\Release[] $releases
 */

use EddSlReleases\ValueObjects;

$buttonColour = edd_get_option('checkout_color', 'blue');
$buttonColour = ($buttonColour === 'inherit') ? '' : $buttonColour;

?>
<p class="edd-sl-releases--back">
    <a href="<?php echo esc_url(remove_query_arg('edd-sl-product')); ?>">
        <?php echo '&laquo; '.esc_html__('Back to downloads', 'edd-sl-releases'); ?>
    </a>
</p>
<?php

if ($releases) {
    ?>
    <div class="edd-sl-releases--release-list">
        <?php foreach ($releases as $release): ?>
            <div class="edd-sl-releases-release">
                <div class="edd-sl-releases-release--header">
                    <div>
                        <h2>
                            <span class="edd-sl-releases-release--version">
                                <?php echo esc_html($release->version); ?>
                            </span>
                            <?php if ($release->pre_release) : ?>
                            <span class="edd-sl-releases-badge edd-sl-releases-badge--pre-release">
                                <?php esc_html_e('Pre Release', 'edd-sl-releases'); ?>
                            </span>
                            <?php endif; ?>
                        </h2>
                        <div class="edd-sl-releases-release--date">
                            <?php
                            echo esc_html(sprintf(
                            /* Translators: %s date of the release */
                                __('Released on %s', 'edd-sl-releases'),
                                date(get_option('date_format'), strtotime($release->created_at))
                            ));
                            ?>
                        </div>

                        <?php if ($release->requirements): ?>
                            <div class="edd-sl-releases-requirements">
                                <?php foreach($release->requirements as $platform => $requirement) : ?>
                                    <span class="edd-sl-releases-requirement edd-sl-releases-requirement--<?php echo esc_attr($platform); ?>">
                                        <span class="edd-sl-releases-requirement--platform">
                                            <?php echo esc_html($platform); ?>
                                        </span>
                                        <span class="edd-sl-releases-requirement--version">
                                            <?php echo esc_html($requirement); ?>
                                        </span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="edd-sl-releases--product-actions">
                        <a
                            href="<?php echo esc_url(new ValueObjects\ReleaseDownloadUrl($release->product_id, $release->version)); ?>"
                            class="edd-submit button <?php echo esc_attr($buttonColour); ?>"
                        >
                            <?php esc_html_e('Download', 'edd-sl-releases'); ?>
                        </a>
                    </div>
                </div>

                <?php if ($release->changelog): ?>
                    <div class="edd-sl-releases-release--body">
                        <?php echo wp_kses_post(wpautop($release->changelog)); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
} else {
    ?>
    <div id="edd-sl-releases-no-releases">
        <p>
            <?php esc_html_e('No releases yet.', 'edd-sl-downloads'); ?>
        </p>
    </div>
    <?php
}
