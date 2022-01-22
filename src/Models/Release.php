<?php
/**
 * Release.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Models;

use EddSlReleases\Traits\CastsAttributes;
use EddSlReleases\Traits\Serializable;

class Release
{
    use Serializable, CastsAttributes;

    public int $id;
    public int $product_id;
    public string $version;
    public int $file_attachment_id;
    public string $file_path;
    public string $file_name;
    public ?string $changelog = null;
    public ?array $requirements = null;
    public bool $pre_release = false;
    public string $created_at;

    protected array $casts = [
        'id'                 => 'int',
        'product_id'         => 'int',
        'version'            => 'string',
        'file_attachment_id' => 'int',
        'file_path'          => 'string',
        'file_name'          => 'string',
        'changelog'          => 'string',
        'requirements'       => 'array',
        'pre_release'        => 'bool',
        'created_at'         => 'string',
    ];

    public function __construct(array $row = [])
    {
        foreach ($row as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $this->castAttribute($property, $value);
            }
        }
    }

    protected function arrayAppend(): array
    {
        return [
            'created_at_display' => date(
                get_option('date_format').' '.get_option('time_format'),
                strtotime($this->created_at)
            )
        ];
    }

}
