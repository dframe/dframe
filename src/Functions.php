<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */


/**
 * @param $path
 *
 * @return array
 */
function pathFile($path)
{
    $folder = '';
    $name = $path;
    if (strpos($path, '/')) {
        $path = explode('/', $path);

        $pathCount = count($path) - 1;
        $folder = '';
        for ($i = 0; $i < $pathCount; $i++) {
            $folder .= $path[$i] . '/';
        }
        $name = $path[$pathCount];
    }

    return [$folder, $name];
}

/**
 * Randomowo generowany string.
 *
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

/**
 * Zmiana Obiektu wielowymiaroa na tablice array.
 *
 * @param $obj
 *
 * @return array
 */
function object_to_array($obj)
{
    $obj = is_object($obj) ? (array)$obj : $obj;

    if (is_array($obj)) {
        $new = [];
        foreach ($obj as $key => $val) {
            $key2 = str_replace("\0", '', $key);
            $new[$key2] = object_to_array($val);
        }
    } else {
        $new = $obj;
    }

    return $new;
}

/**
 * Wyszukiwanie ciagu zdania za pozmoca wilcardu
 * ala ma kota -> ala * kota == TRUE.
 *
 * @param $source
 * @param $pattern
 *
 * @return false|int
 */
function stringMatchWithWildcard($source, $pattern)
{
    $pattern = preg_quote($pattern, '/');
    $pattern = str_replace('\*', '.*', $pattern);

    return preg_match('/^' . $pattern . '$/i', $source);
}
