<?php
/**
 * EddUploadDir.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Traits;

trait EddUploadDir
{

    protected function getEddDirDetails(): array
    {
        add_filter('upload_dir', 'edd_set_upload_dir');
        $upload_dir = wp_upload_dir();
        wp_mkdir_p($upload_dir['path']);

        return $upload_dir;
    }

}
