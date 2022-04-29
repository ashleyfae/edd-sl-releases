<?php
/**
 * CreateAndPublishReleaseTest.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace EddSlReleases\Tests\Unit\Actions;

use EddSlReleases\Actions\CreateAndPublishRelease;
use EddSlReleases\Actions\SyncSoftwareLicensingReleases;
use EddSlReleases\Repositories\ReleaseRepository;
use EddSlReleases\Services\ReleaseFileProcessor;
use EddSlReleases\Tests\Unit\TestCase;
use Mockery;

class CreateAndPublishReleaseTest extends TestCase
{
    /**
     * @covers \EddSlReleases\Actions\CreateAndPublishRelease::withoutEvents()
     */
    public function testWithoutEvents()
    {
        $action = new CreateAndPublishRelease(
            releaseFileProcessor: Mockery::mock(ReleaseFileProcessor::class),
            releaseRepository: Mockery::mock(ReleaseRepository::class),
            productSyncer: Mockery::mock(SyncSoftwareLicensingReleases::class)
        );

        $this->assertTrue($this->getInaccessibleProperty($action, 'withEvents')->getValue($action));

        $action->withoutEvents();

        $this->assertFalse($this->getInaccessibleProperty($action, 'withEvents')->getValue($action));
    }
}
