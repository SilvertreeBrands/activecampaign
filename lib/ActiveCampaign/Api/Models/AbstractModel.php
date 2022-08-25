<?php
declare(strict_types=1);

namespace ActiveCampaign\Api\Models;

abstract class AbstractModel
{
    /**
     * Extract payload
     *
     * @param bool $includeNull
     *
     * @return array
     */
    public function extractPayload(bool $includeNull = false): array
    {
        $properties = get_object_vars($this);

        if (!$includeNull) {
            foreach ($properties as $property => $value) {
                if (is_null($value)) {
                    unset($properties[$property]);
                }
            }
        }

        return $properties;
    }
}
