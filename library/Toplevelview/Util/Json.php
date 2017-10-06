<?php

namespace Icinga\Module\Toplevelview\Util;

use Icinga\Exception\Json\JsonEncodeException;
use Icinga\Util\Json as IcingaJson;

class Json extends IcingaJson
{
    /**
     * {@link json_encode()} wrapper
     *
     * @param   mixed   $value
     * @param   int     $options
     * @param   int     $depth
     *
     * @return  string
     * @throws  JsonEncodeException
     */
    public static function encode($value, $options = 0, $depth = 512)
    {
        if (version_compare(phpversion(), '5.4.0', '<')) {
            $encoded = json_encode($value);
        } else if (version_compare(phpversion(), '5.5.0', '<')) {
            $encoded = json_encode($value, $options);
        } else {
            $encoded = json_encode($value, $options, $depth);
        }
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonEncodeException('%s: %s', static::lastErrorMsg(), var_export($value, true));
        }
        return $encoded;
    }
}
