<?php
/**
 * TestCase.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace EddSlReleases\Tests\Unit;

use EddSlReleases\Tests\Traits\CanAccessInaccessible;
use Exception;
use Mockery;
use Mockery\Expectation;
use Mockery\Mock;
use ReflectionMethod;
use WP_Mock;

abstract class TestCase extends \WP_Mock\Tools\TestCase
{
    use CanAccessInaccessible;

    /**
     * This method is here until this PR is merged:
     *
     * @link https://github.com/10up/wp_mock/issues/158
     */
    protected function getAnnotations(): array
    {
        return \PHPUnit\Util\Test::parseTestMethodAnnotations(
            static::class,
            $this->getName( false )
        );
    }

    /**
     * Mock a static method of a class.
     *
     * Copied from {@see WP_Mock\Tools\TestCase::mockStaticMethod()}.
     * This is overridden until this PR is merged: {@link https://github.com/10up/wp_mock/pull/165}
     *
     * @param  string  $class  The classname or class::method name
     * @param  null|string  $method  The method name. Optional if class::method used for $class
     *
     * @return Expectation
     * @throws Exception
     */
    protected function mockStaticMethod($class, $method = null)
    {
        if (! $method) {
            list($class, $method) = (explode('::', $class) + [null, null]);
        }
        if (! $method) {
            throw new Exception(sprintf('Could not mock %s::%s', $class, $method));
        }
        if (! WP_Mock::usingPatchwork() || ! function_exists('Patchwork\redefine')) {
            throw new Exception('Patchwork is not loaded! Please load patchwork before mocking static methods!');
        }

        $safe_method = "wp_mock_safe_{$method}";
        $signature   = md5("{$class}::{$method}");

        if (! empty($this->mockedStaticMethods[$signature])) {
            $mock = $this->mockedStaticMethods[$signature];
        } else {
            $rMethod = false;
            if (class_exists($class)) {
                $rMethod = new ReflectionMethod($class, $method);
            }
            if (
                $rMethod &&
                (
                    ! $rMethod->isUserDefined() ||
                    ! $rMethod->isStatic() ||
                    $rMethod->isPrivate()
                )
            ) {
                throw new Exception(sprintf('%s::%s is not a user-defined non-private static method!', $class,
                    $method));
            }

            /** @var Mock $mock */
            $mock = Mockery::mock($class);
            $mock->shouldAllowMockingProtectedMethods();
            $this->mockedStaticMethods[$signature] = $mock;

            \Patchwork\redefine("{$class}::{$method}", function () use ($mock, $safe_method) {
                return call_user_func_array([$mock, $safe_method], func_get_args());
            });
        }

        return $mock->shouldReceive($safe_method);
    }
}
