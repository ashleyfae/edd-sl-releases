<?php
/**
 * RecordReleaseDownload.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Actions;

use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\PurchasedProductsRepository;

class RecordReleaseDownload
{
    public function __construct(protected PurchasedProductsRepository $repository)
    {

    }

    public function execute(Release $release)
    {
        $product = new \EDD_SL_Download($release->product_id);

        edd_record_download_in_log(
            $release->product_id,
            $release->pre_release ? $product->get_beta_upgrade_file_key() : $product->get_upgrade_file_key(),
            [],
            edd_get_ip(),
            $this->getOrderIdForProduct($release->product_id),
            0
        );
    }

    protected function getOrderIdForProduct(int $productId): int
    {
        try {
            return $this->repository->getUsersOrderForProduct(
                get_current_user_id(),
                $productId
            )
                ->order_id;
        } catch (\Exception $e) {
            return 0;
        }
    }

}
