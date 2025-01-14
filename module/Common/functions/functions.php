<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\Common;

use const JSON_ERROR_NONE;
use function array_key_exists;
use function array_shift;
use function getenv;
use function in_array;
use function is_array;
use function json_last_error;
use function json_last_error_msg;
use function strtolower;
use function trim;

/**
 * Gets the value of an environment variable. Supports boolean, empty and null.
 * This is basically Laravel's env helper
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 * @link https://github.com/laravel/framework/blob/5.2/src/Illuminate/Foundation/helpers.php#L369
 */
function env($key, $default = null)
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
    }

    return trim($value);
}

function contains($needle, array $haystack): bool
{
    return in_array($needle, $haystack, true);
}

function json_decode(string $json, int $depth = 512, int $options = 0): array
{
    $data = \json_decode($json, true, $depth, $options);
    if (JSON_ERROR_NONE !== json_last_error()) {
        throw new Exception\InvalidArgumentException('Error decoding JSON: ' . json_last_error_msg());
    }

    return $data;
}

function array_path_exists(array $path, array $array): bool
{
    // As soon as a step is not found, the path does not exist
    $step = array_shift($path);
    if (! array_key_exists($step, $array)) {
        return false;
    }

    // Once the path is empty, we have found all the parts in the path
    if (empty($path)) {
        return true;
    }

    // If current value is not an array, then we have not found the path
    $newArray = $array[$step];
    if (! is_array($newArray)) {
        return false;
    }

    return array_path_exists($path, $newArray);
}

function array_get_path(array $path, array $array)
{
    do {
        $step = array_shift($path);
        if (! is_array($array) || ! array_key_exists($step, $array)) {
            return null;
        }

        $array = $array[$step];
    } while (! empty($path));

    return $array;
}
