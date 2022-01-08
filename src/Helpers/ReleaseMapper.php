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

    public function mapBetaData($value, $objectId, $metaKey, bool $single, ?string $metaType = null)
    {
        $betaKeys = [
            '_edd_sl_beta_enabled',
            '_edd_sl_beta_version',
            '_edd_sl_beta_changelog',
            '_edd_sl_beta_upgrade_file_key',
        ];

        if (! in_array($metaKey, $betaKeys, true)) {
            return $value;
        }

        // If we have no beta versions, bail.
        try {
            $latestPreRelease = $this->releaseRepository->getLatestPreRelease((int) $objectId);
        } catch (ModelNotFoundException $e) {
            return $value;
        }

        try {
            $latestStableRelease = $this->releaseRepository->getLatestStableRelease((int) $objectId);

            // If the stable is higher than the beta, bail.
            if (version_compare($latestStableRelease->version, $latestPreRelease->version, '>')) {
                return $value;
            }
        } catch (ModelNotFoundException $e) {

        }

        return match ($metaKey) {
            '_edd_sl_beta_enabled' => true,
            '_edd_sl_beta_version' => $latestPreRelease->version,
            '_edd_sl_beta_changelog' => $latestPreRelease->changelog,
            '_edd_sl_beta_upgrade_file_key' => 0
        };
    }

}
