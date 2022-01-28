<?php
/**
 * File.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\Helpers;

class File
{

    public int $attachmentId;
    public string $filePath;
    public string $name;
    public string $url;
    public string $path;

    public function toArray(): array
    {
        return [
            'name'          => $this->name,
            'file'          => $this->url,
            'condition'     => 0,
            'attachment_id' => $this->attachmentId,
        ];
    }

}
