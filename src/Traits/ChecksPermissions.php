<?php
/**
 * ChecksPermissions.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Traits;

trait ChecksPermissions
{

    public function restPermissionCheck(): \WP_Error|bool
    {
        if (! $this->currentUserCanManageReleases()) {
            return new \WP_Error(
                'rest_forbidden',
                __('You do not have permission to perform this action.', 'edd-sl-releases'),
                ['status' => is_user_logged_in() ? 403 : 401]
            );
        }

        return true;
    }

    public function currentUserCanManageReleases(): bool
    {
        return current_user_can('edit_products');
    }

    protected function hasValidNonce(string $action, array $data): bool
    {
        return wp_verify_nonce(
            $data[$action.'_nonce'] ?? null,
            $action
        );
    }

}
