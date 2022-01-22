<?php
/**
 * ProductMetabox.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Admin;

use EddSlReleases\Helpers\ViewLoader;
use function add_meta_box;

class ProductMetabox
{

    public function __construct(protected ViewLoader $viewLoader)
    {

    }

    public function __invoke(): void
    {
        add_meta_box(
            'edd_sl_releases',
            __('Software Releases', 'edd-sl-releases'),
            [$this, 'render'],
            'download',
            'normal',
            'core'
        );
    }

    public function render(\WP_Post $post): void
    {
        $this->viewLoader->loadView('admin/metabox.php', [
            'post'      => $post,
            'product'   => new \EDD_SL_Download($post->ID),
            'createUrl' => add_query_arg([
                'post_type' => 'download',
                'page'      => 'edd-sl-releases',
                'product'   => urlencode($post->ID),
            ], admin_url('edit.php'))
        ]);
    }

}
