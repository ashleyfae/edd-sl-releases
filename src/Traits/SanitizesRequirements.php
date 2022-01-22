<?php
/**
 * SanitizesRequirements.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Traits;

trait SanitizesRequirements
{

    public function sanitizeRequirements(array|string|null $requirements): ?array
    {
        if (is_string($requirements)) {
            $requirements = json_decode($requirements, true);
        }

        $value = is_array($requirements) && ! empty($requirements)
            ? array_intersect_key((array) $requirements, edd_sl_get_platforms())
            : null;

        return $value ? : null;
    }

}
