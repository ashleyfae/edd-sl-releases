<?php
/**
 * ReleasesShortcode.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
 */

namespace EddSlReleases\Services\Shortcodes;

use EddSlReleases\Helpers\ViewLoader;
use EddSlReleases\Repositories\PurchasedProductsRepository;
use EddSlReleases\Repositories\ReleaseRepository;
use JetBrains\PhpStorm\Pure;

class ReleasesShortcode implements ShortcodeInterface
{
    /**
     * @var array Shortcode arguments.
     */
    protected array $args = [];

    public function __construct(
        protected ViewLoader $viewLoader,
        protected PurchasedProductsRepository $productsRepository,
        protected ReleaseRepository $releaseRepository
    ) {

    }

    /**
     * @inheritDoc
     */
    public static function tag(): string
    {
        return 'edd-sl-releases';
    }

    /**
     * @inheritDoc
     */
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

    /**
     * Whether we're viewing the releases for an individual product.
     *
     * @since 1.0
     *
     * @return bool
     */
    protected function isProductView(): bool
    {
        return ! empty($this->getQueriedProductId());
    }

    /**
     * Returns the ID of the product being queried, or `null` if none.
     *
     * @since 1.0
     *
     * @return int|null
     */
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

    /**
     * Lists the releases for a product.
     *
     * @todo Pagination. Right now only shows 10 latest and that's it.
     *
     * @since 1.0
     *
     * @return void
     */
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

    /**
     * Lists all the (distinct) products a user has purchased that have licensing enabled.
     *
     * @since 1.0
     *
     * @return void
     */
    protected function listPurchasedProducts(): void
    {
        $products = $this->productsRepository->getLicensedProducts(get_current_user_id());

        $this->viewLoader->loadView('releases-shortcode/products.php', [
            'products' => $products
        ]);
    }
}
