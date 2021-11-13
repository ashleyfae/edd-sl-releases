<?php
/**
 * ServiceProvider.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ServiceProviders;

interface ServiceProvider
{

    /**
     * Registers the service provider within the application.
     *
     * @since 1.0
     *
     * @return void
     */

    public function register(): void;

    /**
     * Bootstraps the service after all of the services have been registered.
     * All dependencies will be available at this point.
     *
     * @since 1.0
     *
     * @return void
     */

    public function boot(): void;

}
