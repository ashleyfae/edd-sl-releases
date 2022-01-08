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

    public function permissionCheck()
    {
        if (! current_user_can('edit_products')) {
            return new \WP_Error(
                'rest_forbidden',
                __('You do not have permission to perform this action.', 'edd-sl-releases'),
                ['status' => is_user_logged_in() ? 403 : 401]
            );
        }

        return true;
    }

}
