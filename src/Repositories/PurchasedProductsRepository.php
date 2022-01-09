<?php
/**
 * PurchasedProductsRepository.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Repositories;

class PurchasedProductsRepository
{

    /**
     * @param  int  $userId
     *
     * @return \EDD_Download[]
     */
    public function getPurchasedProducts(int $userId): array
    {
        global $wpdb;

        $productIds = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT product_id
            FROM {$wpdb->edd_order_items} oi
            INNER JOIN {$wpdb->edd_orders} o ON(o.id = oi.order_id)
            WHERE oi.status = 'complete'
            AND o.type = 'sale'
            AND o.user_id = %d",
            $userId
        ));

        if (empty($productIds)) {
            return [];
        }

        $posts = get_posts([
            'post__in'       => $productIds,
            'status'         => 'post_status',
            'posts_per_page' => -1, // @todo Pagination
        ]);

        $products = [];
        foreach ($posts as $post) {
            $products[] = new \EDD_SL_Download($post->ID);
        }

        return array_filter($products, function (\EDD_SL_Download $product) {
            return $product->ID > 0 && $product->licensing_enabled();
        });
    }

}
