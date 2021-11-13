<?php
/**
 * CliServiceProvider.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\ServiceProviders;

use EddSlReleases\CliCommands\CliCommand;
use EddSlReleases\CliCommands\PublishRelease;

class CliServiceProvider implements ServiceProvider
{
    protected array $commands = [
        PublishRelease::class,
    ];

    public function register(): void
    {

    }

    public function boot(): void
    {
        if (! class_exists('WP_CLI')) {
            return;
        }

        foreach ($this->commands as $command) {
            if (! is_subclass_of($command, CliCommand::class)) {
                throw new \RuntimeException(sprintf("{$command} must implement the %s interface.", CliCommand::class));
            }

            \WP_CLI::add_command('eddsl '.$command::commandName(), eddSlReleases()->make($command));
        }
    }
}
