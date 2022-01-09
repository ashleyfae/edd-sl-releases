<?php
/**
 * ReleaseDownloadUrl.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ValueObjects;

class ReleaseDownloadUrl
{

    public function __construct(public int $productId, public string $version)
    {

    }

    public function __toString(): string
    {
        return add_query_arg([
            'edd_action' => 'download_sl_release',
            'product_id' => urlencode($this->productId),
            'version'    => urlencode($this->version),
        ], home_url());
    }

}
