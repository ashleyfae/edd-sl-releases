<?php
/**
 * ShowProductMetabox.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Actions\Admin;

use EddSlReleases\Helpers\ViewLoader;

class ShowProductMetabox
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
            'post'    => $post,
            'product' => new \EDD_SL_Download($post->ID),
        ]);
    }

}
