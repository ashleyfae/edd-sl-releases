<?php
/**
 * AdminPage.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Admin;

use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\Helpers\ViewLoader;
use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;

class AdminPage
{

    public function __construct(
        protected ViewLoader $viewLoader,
        protected ReleaseRepository $releaseRepository
    ) {

    }

    public function register(): void
    {
        add_submenu_page(
            'edit.php?post_type=download',
            __('Release', 'edd-sl-releases'),
            __('Release', 'edd-sl-releases'),
            'edit_products',
            'edd-sl-releases',
            [$this, 'render']
        );

        // Prevent this page from showing up in the menu.
        add_action('admin_head', function () {
            remove_submenu_page('edit.php?post_type=download', 'edd-sl-releases');
        });
    }

    public function render(): void
    {
        if (! empty($_GET['release'])) {
            try {
                $release = $this->releaseRepository->getById(absint($_GET['release']));
            } catch (ModelNotFoundException $e) {
                wp_die(sprintf(
                /* Translators: %d ID of the release */
                    __('No release found with the ID %d.', 'edd-sl-releases'),
                    absint($_GET['release'])
                ));
            }
        } else {
            $release = new Release();
            if (! empty($_GET['product'])) {
                $release->product_id = absint($_GET['product']);

                try {
                    // copy requirements from previous release, as requirements are unlikely to go _down_ and it's nice
                    // to have them as a reference
                    $previousRelease = $this->releaseRepository->getLatestStableRelease($release->product_id);
                    $release->requirements = $previousRelease->requirements;
                } catch(\Exception $e) {
                    // do nothing
                }
            } else {
                wp_die(__('Undefined product.', 'edd-sl-releases'));
            }
        }

        $this->viewLoader->loadView('admin/release.php', [
            'release' => $release,
            'product' => new \EDD_SL_Download($release->product_id),
        ]);
    }

}
