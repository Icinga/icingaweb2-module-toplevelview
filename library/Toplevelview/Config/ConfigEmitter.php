<?php
/* TopLevelView module for Icingaweb2 - Copyright (c) 2017 Icinga Development Team <info@icinga.com> */

namespace Icinga\Module\Toplevelview\Config;

use Icinga\Exception\NotImplementedError;
use stdClass;

class ConfigEmitter
{
    /** @var array */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public static function classToArray($obj)
    {
        $arr = array();
        foreach (get_object_vars($obj) as $k => $v) {
            if ($k !== 'children') {
                $arr[$k] = $v;
            }
        }

        // handle children last for visibility
        if (property_exists($obj, 'children')) {
            $arr['children'] = array();
            foreach ($obj->children as $child) {
                // convert each child to an array
                $arr['children'][] = static::classToArray($child);
            }
        }

        return $arr;
    }

    public static function fromLegacyTree(stdClass $tree)
    {
        return new static(static::classToArray($tree));
    }

    public function emitJSON(&$contentType = null)
    {
        $contentType = 'application/json';
        return json_encode($this->config);
    }

    public function emitYAML(&$contentType = null)
    {
        $contentType = 'application/yaml';
        return yaml_emit($this->config, YAML_UTF8_ENCODING, YAML_LN_BREAK);
    }

    public function emitEXPORT(&$contentType = null)
    {
        $contentType = 'text/plain';
        return var_export($this->config, true);
    }

    public function emit($format, &$contentType = null)
    {
        $funcName = 'emit' . strtoupper($format);
        if (method_exists($this, $funcName)) {
            return $this->$funcName($contentType);
        } else {
            throw new NotImplementedError('format "%s" is not implemented to emit!', $format);
        }
    }
}
