<?php
/**
 * PurchasedProductsRepository.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Repositories;

use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\ValueObjects\ProductOrder;

class PurchasedProductsRepository
{

    public function getLicensedProductIds(int $userId): array
    {
        global $wpdb;

        $tableName = edd_software_licensing()->licenses_db->table_name;

        $productIds = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT download_id
            FROM {$tableName}
            WHERE user_id = %d
            AND status IN ('active', 'inactive')",
            $userId
        ));

        if (empty($productIds)) {
            return [];
        }

        return array_map('intval', $productIds);
    }

    public function getLicensedProducts(int $userId): array
    {
        $productIds = $this->getLicensedProductIds($userId);
        if (empty($productIds)) {
            return [];
        }

        $posts = get_posts([
            'post__in'       => $productIds,
            'post_type'      => 'download',
            'status'         => 'post_status',
            'posts_per_page' => -1, // @todo Pagination
            'orderby'        => 'post_title',
            'order'          => 'asc',
        ]);

        $products = [];
        foreach ($posts as $post) {
            $products[] = new \EDD_SL_Download($post->ID);
        }

        return array_filter($products, function (\EDD_SL_Download $product) {
            return $product->ID > 0 && $product->licensing_enabled();
        });
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getLicenseForProduct(int $userId, int $productId): \EDD_SL_License
    {
        global $wpdb;

        $tableName = edd_software_licensing()->licenses_db->table_name;

        $license = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT *
                FROM {$tableName}
                WHERE user_id = %d
                AND download_id = %d
                AND status IN ('active', 'inactive')
                ORDER BY id DESC
                LIMIT 1",
                $userId,
                $productId
            )
        );

        if (empty($license)) {
            throw new ModelNotFoundException();
        }

        return new \EDD_SL_License($license);
    }

    public function hasActiveLicenseForProduct(int $userId, int $productId): bool
    {
        try {
            $this->getLicenseForProduct($userId, $productId);

            return true;
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getUsersOrderForProduct(int $userId, int $productId): ProductOrder
    {
        $license = $this->getLicenseForProduct($userId, $productId);

        return new ProductOrder(
            $license->payment_id,
            $license->download_id,
            $license->price_id,
            edd_get_payment_user_email($license->payment_id),
            edd_get_payment_key($license->payment_id)
        );
    }

}
