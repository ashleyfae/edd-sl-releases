<?php
/**
 * Serializable.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\Traits;

trait Serializable
{

    protected function arrayAppend(): array
    {
        return [];
    }

    public function toArray(): array
    {
        $data = [];

        /*
		 * get_object_vars() returns non-public properties when used within the class
		 * so we're using a ReflectionClass to get the public properties only.
		 */
        $object = new \ReflectionClass($this);
        foreach ($object->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property instanceof \ReflectionProperty) {
                $data[$property->name] = $this->{$property->name} ?? null;
            }
        }

        return array_merge($data, $this->arrayAppend());
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

}
