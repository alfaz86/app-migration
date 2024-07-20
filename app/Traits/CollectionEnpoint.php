<?php

namespace App\Traits;

trait CollectionEnpoint
{
    function getValueByPath(array $array, string $path)
    {
        $keys = explode('.', $path);
        $current = $array;

        foreach ($keys as $key) {
            // Handle array index like '[0]'
            if (preg_match('/^(.*?)\[(\d+)\]$/', $key, $matches)) {
                $key = $matches[1];
                $index = (int) $matches[2];
                $current = $current[$key][$index] ?? null;
            } else {
                $current = $current[$key] ?? null;
            }

            // If current is null, break the loop
            if ($current === null) {
                break;
            }
        }

        return $current;
    }

    function setObjectData($resultData, $responseAPI)
    {
        if ($resultData == 'current') {
            return $responseAPI;
        } elseif (strpos($resultData, '.') !== false) {
            $keys = explode('.', $resultData);
            $specificObject = $responseAPI;

            foreach ($keys as $key) {
                if (isset($specificObject[$key])) {
                    $specificObject = $specificObject[$key];
                } else {
                    $specificObject = null;
                    break;
                }
            }

            return is_array($specificObject) ? $specificObject : null;
        } else {
            return isset($responseAPI[$resultData]) ? $responseAPI[$resultData] : null;
        }
    }
}
