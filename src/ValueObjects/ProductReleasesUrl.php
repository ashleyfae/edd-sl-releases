<?php
/**
 * ProductReleasesUrl.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ValueObjects;

class ProductReleasesUrl
{

    public function __construct(public int $productId)
    {

    }

    public function __toString(): string
    {
        return add_query_arg([
            'edd-sl-product' => urlencode($this->productId),
        ], edd_get_current_page_url());
    }

}
