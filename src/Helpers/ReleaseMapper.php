<?php
/**
 * ReleaseMapper.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Helpers;

use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\Repositories\ReleaseRepository;

class ReleaseMapper
{
    protected ReleaseRepository $releaseRepository;

    public function __construct(ReleaseRepository $releaseRepository)
    {
        $this->releaseRepository = $releaseRepository;
    }

    public function mapVersion($version, \EDD_SL_Download $product): string
    {
        try {
            return $this->releaseRepository->getLatestStableRelease($product->ID)->version;
        } catch (ModelNotFoundException $e) {
            return $version;
        }
    }

    public function mapChangelog($changelog, $productId): string
    {
        try {
            return $this->releaseRepository->getLatestStableRelease((int) $productId)->version;
        } catch (ModelNotFoundException $e) {
            return $changelog;
        }
    }

    public function mapRequirements($value, $objectId, $metaKey, bool $single, ?string $metaType = null)
    {
        if ($metaKey !== '_edd_sl_required_versions') {
            return $value;
        }

        try {
            return $this->releaseRepository->getLatestStableRelease((int) $objectId)->requirements ? : [];
        } catch (ModelNotFoundException $e) {
            return $value;
        }
    }

}
