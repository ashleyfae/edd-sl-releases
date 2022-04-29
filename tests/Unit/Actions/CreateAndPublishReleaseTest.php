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
use EddSlReleases\Models\Release;
use EddSlReleases\Repositories\ReleaseRepository;
use EddSlReleases\Services\ReleaseFileProcessor;
use EddSlReleases\Tests\Unit\TestCase;
use EddSlReleases\ValueObjects\PreparedReleaseFile;
use Generator;
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

    /**
     * @covers       \EddSlReleases\Actions\CreateAndPublishRelease::execute()
     * @dataProvider providerCanExecute
     */
    public function testCanExecute(array $args, bool $withEvents, ?string $expectedException)
    {
        $action = $this->createPartialMock(CreateAndPublishRelease::class, ['makePreparedFile']);

        if (! empty($expectedException)) {
            $this->expectException($expectedException);
        }

        $release = new Release(['product_id' => 5]);

        $repo = Mockery::mock(ReleaseRepository::class);
        $repo->expects('insert')
            ->times($expectedException ? 0 : 1)
            ->andReturn($release);

        $syncer = Mockery::mock(SyncSoftwareLicensingReleases::class);
        $syncer->expects('execute')
            ->times($withEvents && ! $expectedException ? 1 : 0)
            ->with(5);

        $this->setInaccessibleProperty($action, 'releaseRepository', $repo);
        $this->setInaccessibleProperty($action, 'withEvents', $withEvents);
        $this->setInaccessibleProperty($action, 'productSyncer', $syncer);

        $this->assertSame($release, $action->execute($args));
    }

    /** @see testCanExecute */
    public function providerCanExecute(): Generator
    {
        yield 'valid args with events' => [
            'args'              => ['file_name' => 'my-file.zip'],
            'withEvents'        => true,
            'expectedException' => null,
        ];

        yield 'valid args without events' => [
            'args'              => ['file_name' => 'my-file.zip'],
            'withEvents'        => false,
            'expectedException' => null,
        ];

        yield 'invalid args' => [
            'args'              => ['data'],
            'withEvents'        => true,
            'expectedException' => \InvalidArgumentException::class,
        ];
    }

    /**
     * @covers       \EddSlReleases\Actions\CreateAndPublishRelease::makePreparedFile()
     * @dataProvider providerCanMakePreparedFile
     */
    public function testCanMakePreparedFile(
        array $args,
        bool $hasFile,
        bool $expectedFromGit,
        bool $expectedFromZip = false,
        bool $expectedFromAttachmentId = false,
        bool $expectedFromFilePath = false,
        ?string $expectedException = null
    ) {
        $action = $this->createPartialMock(
            CreateAndPublishRelease::class,
            ['makeFromGitAssetUrl', 'makeFromZipFile', 'makeFromAttachmentId', 'makeFromFilePath']
        );

        if ($expectedException) {
            $this->expectException($expectedException);
        }

        if ($hasFile) {
            $_FILES['file_zip'] = 'zip';
        } else {
            unset($_FILES['file_zip']);
        }

        $release = new PreparedReleaseFile('file/path.zip');

        $action->expects($expectedFromGit ? $this->once() : $this->never())
            ->method('makeFromGitAssetUrl')
            ->with($args)
            ->willReturn($release);

        $action->expects($expectedFromZip ? $this->once() : $this->never())
            ->method('makeFromZipFile')
            ->with($args)
            ->willReturn($release);

        $action->expects($expectedFromAttachmentId ? $this->once() : $this->never())
            ->method('makeFromAttachmentId')
            ->with($args)
            ->willReturn($release);

        $action->expects($expectedFromFilePath ? $this->once() : $this->never())
            ->method('makeFromFilePath')
            ->with($args)
            ->willReturn($release);

        $this->assertSame($release, $this->invokeInaccessibleMethod($action, 'makePreparedFile', $args));
    }

    /** @see testCanMakePreparedFile */
    public function providerCanMakePreparedFile(): Generator
    {
        yield 'git asset' => [
            'args'            => ['git_asset_url' => true],
            'hasFile'         => false,
            'expectedFromGit' => true,
        ];

        yield 'zip asset' => [
            'args'            => [],
            'hasFile'         => true,
            'expectedFromGit' => false,
            'expectedFromZip' => true,
        ];

        yield 'attachment ID' => [
            'args'                     => ['file_attachment_id' => 1],
            'hasFile'                  => false,
            'expectedFromGit'          => false,
            'expectedFromZip'          => false,
            'expectedFromAttachmentId' => true,
        ];

        yield 'file path' => [
            'args'                     => ['file_path' => 1],
            'hasFile'                  => false,
            'expectedFromGit'          => false,
            'expectedFromZip'          => false,
            'expectedFromAttachmentId' => false,
            'expectedFromFilePath'     => true,
        ];

        yield 'invalid arguments' => [
            'args'                     => [],
            'hasFile'                  => false,
            'expectedFromGit'          => false,
            'expectedFromZip'          => false,
            'expectedFromAttachmentId' => false,
            'expectedFromFilePath'     => false,
            'expectedException'        => \InvalidArgumentException::class,
        ];
    }
}
