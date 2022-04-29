<?php
/**
 * FileFactory.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\Feature\Helpers\Factories;

use EddSlReleases\Tests\Feature\Helpers\File;

class FileFactory
{

    public static function make(array $args = []): File
    {
        $args = wp_parse_args($args, [
            'file_path' => '/tmp/file.zip',
            'name'      => 'my-file-2.zip',
            'url'       => 'http://localhost/simple-file1.jpg',
        ]);

        $attachmentId = (new \WP_UnitTest_Factory())->attachment->create();

        update_post_meta($attachmentId, '_wp_attached_file', $args['file_path']);

        $file               = new File();
        $file->attachmentId = (int) $attachmentId;
        $file->name         = $args['name'];
        $file->url          = $args['url'];
        $file->path         = $args['file_path'];

        return $file;
    }

}
