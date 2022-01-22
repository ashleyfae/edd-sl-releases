<?php
/**
 * UpdateRelease.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Actions\Admin;

use EddSlReleases\Exceptions\ModelNotFoundException;
use EddSlReleases\Repositories\ReleaseRepository;
use EddSlReleases\Traits\ChecksPermissions;
use EddSlReleases\Traits\SanitizesRequirements;
use EddSlReleases\Traits\ValidatesReleaseData;

class UpdateRelease
{
    use ValidatesReleaseData, ChecksPermissions, SanitizesRequirements;

    public function __construct(protected ReleaseRepository $releaseRepository)
    {

    }

    public function __invoke($data): void
    {
        try {
            if (! $this->hasValidNonce('edd_sl_update_release', $data)) {
                throw new \Exception(__('You do not have permission to perform this action.', 'edd-sl-releases'));
            }

            if (! $this->currentUserCanManageReleases()) {
                throw new \Exception(__('You do not have permission to perform this action.', 'edd-sl-releases'));
            }

            $this->validateReleaseFields($data);

            if (empty($data['id'])) {
                throw new \Exception(__('Missing release ID.', 'edd-sl-releases'));
            }

            $release = $this->releaseRepository->getById($data['id']);

            // @todo Maybe move some of this sanitization stuff somewhere else. There's some annoying duplication between this and the CreateRelease action.
            $release->setUp([
                'version'            => sanitize_text_field($data['version']),
                'file_attachment_id' => absint($data['file_attachment_id']),
                'file_name'          => sanitize_text_field($data['file_name']),
                'changelog'          => ! empty($data['changelog']) ? wp_kses_post($data['changelog']) : null,
                'requirements'       => $this->sanitizeRequirements($data['requirements'] ?? null),
                'pre_release'        => ! empty($data['pre_release']),
                'released_at'        => date('Y-m-d H:i:s', strtotime($data['released_at'])),
            ]);

            $this->releaseRepository->update($release);

            wp_safe_redirect(
                add_query_arg('edd-sl-releases-notice', 'release-updated', $release->getEditUrl())
            );
            exit;
        } catch (ModelNotFoundException $e) {
            wp_die(__('Invalid release.', 'edd-sl-releases'));
        } catch (\Exception $e) {
            // @todo better error handling because wp_die() sucks for UX.
            wp_die(
                $e->getMessage() ? : __('An unexpected error has occurred.', 'edd-sl-releases')
            );
        }
    }

}
