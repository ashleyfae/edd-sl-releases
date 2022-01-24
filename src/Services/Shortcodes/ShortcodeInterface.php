<?php
/**
 * ShortcodeInterface.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
 */

namespace EddSlReleases\Services\Shortcodes;

interface ShortcodeInterface
{

    /**
     * Name of the shortcode tag.
     *
     * @return string
     */
    public static function tag(): string;

    /**
     * Returns the contents of the shortcode.
     *
     * @param  array  $args
     * @param  mixed|null  $content
     *
     * @return string
     */
    public function render(array $args, mixed $content = null): string;

}
