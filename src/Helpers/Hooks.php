<?php
/**
 * Hooks.php
 *
 * Taken from GiveWP.
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Helpers;

class Hooks
{

    public static function addAction(
        string $tag,
        string $class,
        string $method = '__invoke',
        int $priority = 10,
        int $acceptedArgs = 1
    ) {
        if (! method_exists($class, $method)) {
            throw new \InvalidArgumentException("The method {$method} does not exist on {$class}");
        }

        add_action(
            $tag,
            static function () use ($tag, $class, $method) {
                call_user_func_array([eddSlReleases($class), $method], func_get_args());
            },
            $priority,
            $acceptedArgs
        );
    }

    public static function addFilter(
        string $tag,
        string $class,
        string $method = '__invoke',
        int $priority = 10,
        int $acceptedArgs = 1
    ) {
        if (! method_exists($class, $method)) {
            throw new \InvalidArgumentException("The method {$method} does not exist on {$class}");
        }

        add_filter(
            $tag,
            static function () use ($tag, $class, $method) {
                return call_user_func_array([eddSlReleases($class), $method], func_get_args());
            },
            $priority,
            $acceptedArgs
        );
    }

}
