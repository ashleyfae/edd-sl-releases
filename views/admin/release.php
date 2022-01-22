<?php
/**
 * release.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 *
 * @var \EddSlReleases\Models\Release $release
 * @var EDD_SL_Download $product
 */

use EddSlReleases\ValueObjects\ReleaseDownloadUrl;

$productUrl  = add_query_arg([
    'post'   => urlencode($product->ID),
    'action' => 'edit',
], admin_url('post.php'));
$productLink = '<a href="'.esc_url($productUrl).'">'.esc_html($product->get_name()).'</a>';
?>
<div class="wrap">
    <h1>
        <?php
        if (isset($release->id)) {
            /* Translators: %s name of the product */
            printf(esc_html__('Edit "%s" Release', 'edd-sl-releases'), $productLink);
        } else {
            /* Translators: %s name of the product */
            printf(esc_html__('Create "%s" Release', 'edd-sl-releases'), $productLink);
        }
        ?>
    </h1>

    <form method="POST">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="edd-sl-releases-version">
                        <?php esc_html_e('Version', 'edd-sl-releases'); ?>
                    </label>
                </th>
                <td>
                    <input
                        type="text"
                        id="edd-sl-releases-version"
                        class="regular-text"
                        name="version"
                        value="<?php echo esc_attr($release->version ?? ''); ?>"
                        required
                    >
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php esc_html_e('Pre Release', 'edd-sl-releases'); ?>
                </th>
                <td>
                    <input
                        type="checkbox"
                        id="edd-sl-releases-pre-release"
                        name="pre_release"
                        value="1"
                        <?php checked($release->pre_release); ?>
                    >
                    <label for="edd-sl-releases-pre-release">
                        <?php esc_html_e('Yes', 'edd-sl-releases'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php esc_html_e('File', 'edd-sl-releases'); ?>
                </th>
                <td>
                    <div class="edd-sl-releases-file">
                        <div class="edd-sl-releases-file--filename">
                            <label for="edd-sl-releases-file-name">
                                <?php esc_html_e('File Name', 'edd-sl-releases'); ?>
                            </label>
                            <input
                                type="text"
                                id="edd-sl-releases-file-name"
                                class="regular-text"
                                name="file_name"
                                value="<?php echo esc_attr($release->file_name ?? ''); ?>"
                                required
                            >
                        </div>

                        <?php if (! empty($release->version)) : ?>
                            <div class="edd-sl-releases-file--download-url">
                                <label for="edd-sl-releases-download-url">
                                    <?php esc_html_e('Download URL', 'edd-sl-releases'); ?>
                                </label>
                                <input
                                    type="url"
                                    id="edd-sl-releases-download-url"
                                    class="regular-text"
                                    value="<?php echo esc_attr(new ReleaseDownloadUrl($release->product_id,
                                        $release->version)); ?>"
                                    readonly
                                >
                            </div>
                        <?php endif; ?>

                        <div class="edd-sl-releases-file--upload">
                            <input
                                type="hidden"
                                id="edd-sl-releases-attachment-id"
                                name="file_attachment_id"
                                value="<?php echo esc_attr($release->file_attachment_id ?? ''); ?>"
                            >

                            <button
                                type="button"
                                class="button button-secondary edd-sl-releases--upload"
                                data-id-el="edd-sl-releases-attachment-id"
                            >
                                <?php
                                echo ! empty($release->file_attachment_id)
                                    ? esc_html__('Change File', 'edd-sl-releases')
                                    : esc_html__('Upload File', 'edd-sl-releases');
                                ?>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="edd-sl-releases-changelog">
                        <?php esc_html_e('Changelog', 'edd-sl-releases'); ?>
                    </label>
                </th>
                <td>
                    <?php
                    wp_editor(
                        stripslashes($release->changelog ?? ''),
                        'edd-sl-releases-changelog',
                        [
                            'textarea_name' => 'changelog',
                            'media_buttons' => false,
                            'textarea_rows' => 15,
                        ]
                    );
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php esc_html_e('Requirements', 'edd-sl-releases'); ?>
                </th>
                <td>
                    <?php foreach (edd_sl_get_platforms() as $key => $label) : ?>
                        <div class="edd-form-group">
                            <div class="edd-form-group__control">
                                <label
                                    for="edd-sl-release-requirement-<?php echo esc_attr($key); ?>"
                                    class="edd-form-group__label"
                                >
                                    <?php
                                    /* Translators: %s platform name */
                                    printf(esc_html__('%s Version Required:', 'edd-sl-releases'), $label);
                                    ?>
                                </label>
                                <?php
                                echo EDD()->html->text(
                                    [
                                        'name'  => 'requirements['.$key.']',
                                        'id'    => 'edd-sl-release-requirement-'.$key,
                                        'value' => is_array($release->requirements)
                                            ? ($release->requirements[$key] ?? '')
                                            : '',
                                        'class' => 'edd-form-group__input regular-text',
                                    ]
                                );
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="edd-sl-releases-created-at">
                        <?php esc_html_e('Release Date', 'edd-sl-releases'); ?>
                    </label>
                </th>
                <td>
                    <input
                        type="text"
                        id="edd-sl-releases-created-at"
                        name="released_at"
                        value="<?php echo esc_attr($release->released_at ?? date('Y-m-d H:i:s')); ?>"
                    >
                </td>
            </tr>
            </tbody>
        </table>

        <?php if (! empty($release->id)) : ?>
            <input type="hidden" name="edd_action" value="update_sl_release">
            <input type="hidden" name="id" value="<?php echo esc_attr($release->id); ?>">
            <?php wp_nonce_field('edd_sl_update_release', 'edd_sl_update_release_nonce'); ?>
        <?php else : ?>
            <input type="hidden" name="edd_action" value="create_sl_release">
            <?php wp_nonce_field('edd_sl_create_release', 'edd_sl_create_release_nonce'); ?>
        <?php endif; ?>

        <input type="hidden" name="product_id" value="<?php echo esc_attr($release->product_id); ?>">

        <?php
        submit_button(
            ! empty($release->id) ? __('Save Release', 'edd-sl-release') : __('Publish Release', 'edd-sl-release')
        );
        ?>
    </form>
</div>
