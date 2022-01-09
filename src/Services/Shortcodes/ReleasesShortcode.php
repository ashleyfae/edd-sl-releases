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
use JetBrains\PhpStorm\Pure;

class ReleasesShortcode implements ShortcodeInterface
{
    protected array $args = [];

    public function __construct(
        protected ViewLoader $viewLoader,
        protected PurchasedProductsRepository $productsRepository
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

    #[Pure]
    protected function isProductView(): bool
    {
        return ! empty($this->getQueriedProductId());
    }

    #[Pure]
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
        $this->viewLoader->loadView('releases-shortcode/releases.php');
    }

    protected function listPurchasedProducts(): void
    {
        $products = $this->productsRepository->getLicensedProducts(get_current_user_id());

        $this->viewLoader->loadView('releases-shortcode/products.php', [
            'products' => $products
        ]);
    }
}
