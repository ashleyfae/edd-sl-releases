<?php
/**
 * ShortcodeInterface.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Services\Shortcodes;

interface ShortcodeInterface
{

    public static function tag(): string;

    public function render(array $args, mixed $content = null): string;

}
