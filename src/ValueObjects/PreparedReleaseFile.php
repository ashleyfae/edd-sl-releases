<?php
/**
 * PreparedReleaseFile.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ValueObjects;

use EddSlReleases\Traits\Serializable;

class PreparedReleaseFile
{
    use Serializable;

    public function __construct(public string $file_path, public ?int $file_attachment_id = null)
    {

    }

}
