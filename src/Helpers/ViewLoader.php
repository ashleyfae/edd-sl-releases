<?php
/**
 * ViewLoader.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Helpers;

class ViewLoader
{

    public function getViewDirectory(): string
    {
        return trailingslashit(dirname(EDD_SL_RELEASES_FILE)).'views/';
    }

    /**
     * @param  string  $view  Path to the view.
     * @param  array  $vars  Variables to make available in the view.
     *
     * @return void
     */
    public function loadView(string $view, array $vars = []): void
    {
        extract($vars);

        if (file_exists($this->getViewDirectory().$view)) {
            include $this->getViewDirectory().$view;
        } else {
            throw new \RuntimeException(sprintf('View does not exist: %s', $view));
        }
    }

}
