<?php
/**
 * ReleaseFactory.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\Feature\Helpers\Factories;

use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;

class ReleaseFactory
{

    public static function make(array $args = []): Release
    {
        return eddSlReleases(ReleaseRepository::class)->insert(wp_parse_args($args, [
            'product_id'         => 1,
            'version'            => '1.0',
            'file_attachment_id' => 2,
            'file_path'          => '/path/to/file.zip',
            'file_name'          => 'file.zip',
            'pre_release'        => 0,
        ]));
    }

}
