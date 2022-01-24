<?php
/**
 * ReleasesShortcode.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Services\Shortcodes;

use EddSlReleases\Helpers\ViewLoader;
use EddSlReleases\Repositories\PurchasedProductsRepository;
use EddSlReleases\Repositories\ReleaseRepository;
use JetBrains\PhpStorm\Pure;

class ReleasesShortcode implements ShortcodeInterface
{
    protected array $args = [];

    public function __construct(
        protected ViewLoader $viewLoader,
        protected PurchasedProductsRepository $productsRepository,
        protected ReleaseRepository $releaseRepository
    ) {

    }

    public static function tag(): string
    {
        return 'edd-sl-releases';
    }

    public function render(array $args, mixed $content = null): string
    {
        // @todo login form?
        if (! is_user_logged_in()) {
            return '';
        }

        $this->args = $args;

        ob_start();

        if ($this->isProductView()) {
            $this->listReleasesForProduct();
        } else {
            $this->listPurchasedProducts();
        }

        return ob_get_clean();
    }

    protected function isProductView(): bool
    {
        return ! empty($this->getQueriedProductId());
    }

    protected function getQueriedProductId(): ?int
    {
        if (is_numeric($this->args['product'] ?? null)) {
            return absint($this->args['product'] ?? 0);
        }

        if (is_numeric($_GET['edd-sl-product'] ?? null)) {
            return absint($_GET['edd-sl-product'] ?? 0);
        }

        return null;
    }

    protected function listReleasesForProduct(): void
    {
        /*
         * This is commented out for now because I can't yet decide if I want it. It is set up so that
         * if a user doesn't have an active license key then they can't _download_ the release. But right now
         * they can still view it if they try. :shrug:
         */
        /*if (! $this->productsRepository->hasActiveLicenseForProduct(get_current_user_id(), $this->getQueriedProductId())) {
            $this->viewLoader->loadView('releases-shortcode/no-permission.php');
        }*/

        $this->viewLoader->loadView('releases-shortcode/releases.php', [
            'releases' => $this->releaseRepository->listForProduct($this->getQueriedProductId())
        ]);
    }

    protected function listPurchasedProducts(): void
    {
        $products = $this->productsRepository->getLicensedProducts(get_current_user_id());

        $this->viewLoader->loadView('releases-shortcode/products.php', [
            'products' => $products
        ]);
    }
}
