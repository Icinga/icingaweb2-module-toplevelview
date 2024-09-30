<?php

namespace Tests\Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\Tree\TLVHostGroupNode;

use PHPUnit\Framework\TestCase;
use stdClass;
use ReflectionClass;

final class TLVHostGroupNodeTest extends TestCase
{
    public function testGetTitle()
    {
        $n = new TLVHostGroupNode();
        $n->setProperties(['hostgroup'=>'unit']);
        $this->assertSame('unit', $n->getKey());

        $mockRoot = new class {
            public function getFetched($type, $key) {
                $h = new stdClass;
                $h->display_name = 'host';
            }
        };

        $reflection = new ReflectionClass($n);
        $reflection_root = $reflection->getProperty('root');
        $reflection_root->setAccessible(true);
        $reflection_root->setValue($n, $mockRoot);

        $this->assertSame('unit', $n->getTitle());
    }
}
