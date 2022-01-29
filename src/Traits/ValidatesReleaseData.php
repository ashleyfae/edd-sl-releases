<?php
/**
 * ValidatesReleaseData.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
 */

namespace EddSlReleases\Traits;

trait ValidatesReleaseData
{

    /**
     * @throws \Exception
     */
    protected function validateReleaseFields(array $data)
    {
        $required = [
            'product_id',
            'version',
            'file_attachment_id',
            'file_name',
            'released_at',
        ];

        $missing = array_diff($required, array_keys($data));

        if (! empty($missing)) {
            throw new \Exception(sprintf(
            /* Translators: %s - list of missing fields */
                __('Missing the following required fields: %s', 'edd-sl-releases'),
                json_encode(array_values($missing))
            ));
        }
    }

}
