<?php
/**
 * Plugin.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases;

use EddSlReleases\Container\Container;
use EddSlReleases\ServiceProviders\ApiServiceProvider;
use EddSlReleases\ServiceProviders\ApplicationServiceProvider;
use EddSlReleases\ServiceProviders\CliServiceProvider;
use EddSlReleases\ServiceProviders\DatabaseServiceProvider;

/**
 * @mixin Container
 */
class Plugin
{
    const VERSION = '0.1';

    private Container $container;

    /**
     * @var array Service providers to boot.
     */
    private array $serviceProviders = [
        ApplicationServiceProvider::class,
        DatabaseServiceProvider::class,
        ApiServiceProvider::class,
        CliServiceProvider::class,
    ];

    private bool $serviceProvidersLoaded = false;

    public function __construct()
    {
        $this->container = new Container();
    }

    /**
     * @throws \Exception
     */
    public function __get(string $property)
    {
        return $this->container->get($property);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->container, $name], $arguments);
    }

    public function boot(): void
    {
        $this->loadServiceProviders();
    }

    private function loadServiceProviders(): void
    {
        if ($this->serviceProvidersLoaded) {
            return;
        }

        $providers = [];
        foreach ($this->serviceProviders as $serviceProvider) {
            if (! is_subclass_of($serviceProvider, \EddSlReleases\ServiceProviders\ServiceProvider::class)) {
                throw new \InvalidArgumentException("{$serviceProvider} class must implement the ServiceProvider interface.");
            }

            $serviceProvider = new $serviceProvider;
            $serviceProvider->register();
            $providers[] = $serviceProvider;
        }

        foreach ($providers as $serviceProvider) {
            $serviceProvider->boot();
        }

        $this->serviceProvidersLoaded = true;
    }

}
