<?php
/**
 * TestCase.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Tests\Feature;

use EddSlReleases\Database\ReleasesTable;
use EddSlReleases\Tests\Traits\CanAccessInaccessible;

/**
 * @mixin \PHPUnit\Framework\TestCase
 */
abstract class TestCase extends \WP_UnitTestCase
{
    use CanAccessInaccessible;

    public function set_up()
    {
        wp_set_current_user(0);
    }

    /**
     * Runs once at the end of each class.
     *
     * Delete all data from custom tables.
     */
    public static function tear_down_after_class()
    {
        parent::tear_down_after_class();

        eddSlReleases(ReleasesTable::class)->truncate();
    }


}
