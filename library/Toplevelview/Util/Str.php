<?php

namespace Icinga\Module\Toplevelview\Util;

class Str
{
    /**
     * Str::limit method truncates the given string to the specified length.
     *
     * @param   string $str
     * @param   int    $len
     * @param   string $end
     *
     * @return  string
     */
    public static function limit($str, $len = 25, $end = '...'): string
    {
       // If the string is smaller or equal to the limit we simply return it
        if (mb_strwidth($str, 'UTF-8') <= $len) {
            return $str;
        }

        // If the string is longer than the limit we truncate it
        // and add the given end to it.
        return mb_strimwidth($str, 0, $len, '', 'UTF-8') . $end;
    }
}
