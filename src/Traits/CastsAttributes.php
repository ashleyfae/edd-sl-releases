<?php
/**
 * CastsAttributes.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Traits;

trait CastsAttributes
{

    /**
     * Casts a property to its designated type.
     *
     * @param  string  $propertyName
     * @param $value
     *
     * @return bool|float|int|mixed|string|null
     */
    protected function castAttribute(string $propertyName, $value)
    {
        if (! array_key_exists($propertyName, $this->casts)) {
            return $value;
        }

        // Let null be null.
        if (is_null($value)) {
            return null;
        }

        switch ($this->casts[$propertyName]) {
            case 'array' :
                return is_array($value) ? $value : json_decode($value, true);
            case 'bool' :
                return (bool) $value;
            case 'float' :
                return (float) $value;
            case 'int' :
                return (int) $value;
            case 'string' :
                return (string) $value;
            default :
                return $value;
        }
    }

}
