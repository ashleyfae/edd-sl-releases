<?php
/**
 * DownloadReleaseFile.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Actions;

use EddSlReleases\Exceptions\FileDownloadException;
use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\PurchasedProductsRepository;
use EddSlReleases\Repositories\ReleaseRepository;

class DownloadReleaseFile
{

    public function __construct(
        protected ReleaseRepository $releaseRepository,
        protected PurchasedProductsRepository $productsRepository
    ) {

    }

    public function __invoke($data = []): void
    {
        if (empty($data['product_id'])) {
            wp_die(__('Missing product ID.', 'edd-sl-releases'));
        }

        if (empty($data['version'])) {
            wp_die(__('Missing version.', 'edd-sl-releases'));
        }

        if (! is_user_logged_in()) {
            wp_die(__('You must be logged in to download this file.', 'edd-sl-releases'));
        }

        if (! $this->productsRepository->hasActiveLicenseForProduct(get_current_user_id(), $data['product_id'])) {
            wp_die(__('You do not have an active license key for this product.', 'edd-sl-releases'));
        }

        try {
            try {
                $release = $this->releaseRepository->getByProductVersion(
                    intval($data['product_id']),
                    sanitize_text_field($data['version'])
                );

                $this->processDownload($release);
            } catch (ModelNotFoundException $e) {
                $this->processDownloadFromNonReleaseFile(
                    get_current_user_id(),
                    $data['product_id']
                );
                // @todo try to use SL directly?
                wp_die(__('No such release.', 'edd-sl-releases'));
            }
        } catch (FileDownloadException $e) {
            wp_die(__('Failed to download file. Please contact customer support.', 'edd-sl-releases'));
        } catch (\Exception $e) {
            wp_die(__('An unexpected error occurred.', 'edd-sl-releases'));
        }
    }

    /**
     * @todo Better sync up with EDD core.
     * @see edd_process_download()
     *
     * @param  Release  $release
     *
     * @return void
     * @throws FileDownloadException
     */
    protected function processDownload(Release $release): void
    {
        if (get_post_type($release->file_attachment_id) !== 'attachment') {
            throw new FileDownloadException('This release does not have an attachment.');
        }

        if (! file_exists($release->file_path)) {
            throw new FileDownloadException('This file does not exist.');
        }

        $fileExtension = edd_get_file_extension($release->file_path);
        $ctype         = edd_get_file_ctype($fileExtension);

        @session_write_close();
        nocache_headers();
        header('Robots: none');
        header('Content-Type: '.$ctype);
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="'.$release->file_name.'"');
        header('Content-Transfer-Encoding: binary');
        header("Content-Length: ".@filesize($release->file_path));

        edd_deliver_download($release->file_path);
    }

    /**
     * @throws ModelNotFoundException|FileDownloadException|ModelNotFoundException
     */
    protected function processDownloadFromNonReleaseFile(int $userId, int $productId): void
    {
        $product = new \EDD_SL_Download($productId);
        if (! $product->ID) {
            throw new FileDownloadException('Invalid product ID.');
        }

        if (! $product->get_files()) {
            throw new FileDownloadException('This product does not have any downloadable files.');
        }

        $order = $this->productsRepository->getUsersOrderForProduct($userId, $productId);

        wp_safe_redirect(
            edd_get_download_file_url(
                $order->order_key,
                $order->order_email,
                array_key_first($product->get_files()),
                $order->product_id,
                $order->price_id
            )
        );

        exit;
    }

}
