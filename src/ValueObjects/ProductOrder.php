<?php
/**
 * ProductOrder.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ValueObjects;

class ProductOrder
{

    public function __construct(
        public int $order_id,
        public int $product_id,
        public int $price_id,
        public string $order_email,
        public string $order_key
    ) {

    }

}
