<?php
/**
 * CliCommand.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\CliCommands;

interface CliCommand
{

    public static function commandName(): string;

    public function __invoke(array $args, array $assocArgs): void;

}
