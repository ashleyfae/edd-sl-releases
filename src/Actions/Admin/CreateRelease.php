<?php
/**
 * CreateRelease.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Actions\Admin;

use EddSlReleases\Actions\CreateAndPublishRelease;
use EddSlReleases\Traits\ChecksPermissions;
use EddSlReleases\Traits\SanitizesRequirements;
use EddSlReleases\Traits\ValidatesReleaseData;

class CreateRelease
{
    use ValidatesReleaseData, ChecksPermissions, SanitizesRequirements;

    public function __construct(
        protected CreateAndPublishRelease $releasePublisher
    ) {

    }

    public function __invoke($data): void
    {
        try {
            if (! $this->hasValidNonce('edd_sl_create_release', $data)) {
                throw new \Exception(__('You do not have permission to perform this action.', 'edd-sl-releases'));
            }

            if (! $this->currentUserCanManageReleases()) {
                throw new \Exception(__('You do not have permission to perform this action.', 'edd-sl-releases'));
            }

            $this->validateReleaseFields($data);

            $data['requirements'] = $this->sanitizeRequirements($data['requirements'] ?? null);

            $release = $this->releasePublisher->execute($data);

            wp_safe_redirect(
                add_query_arg('edd-sl-releases-notice', 'release-created', $release->getEditUrl())
            );
            exit;
        } catch (\Exception $e) {
            // @todo better error handling because wp_die() sucks for UX.
            wp_die(
                $e->getMessage() ? : __('An unexpected error has occurred.', 'edd-sl-releases')
            );
        }
    }

}
