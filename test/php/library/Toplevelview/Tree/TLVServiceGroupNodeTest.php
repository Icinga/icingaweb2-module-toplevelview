<?php

namespace Tests\Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\Tree\TLVServiceGroupNode;

use PHPUnit\Framework\TestCase;
use stdClass;
use ReflectionClass;

final class TLVServiceGroupNodeTest extends TestCase
{
    public function testGetTitle()
    {
        $n = new TLVServiceGroupNode();
        $n->setProperties(['servicegroup'=>'unit']);
        $this->assertSame('unit', $n->getKey());

        $mockRoot = new class {
            public function getFetched($type, $key) {
                $h = new stdClass;
                $h->display_name = 'service';
            }
        };

        $reflection = new ReflectionClass($n);
        $reflection_root = $reflection->getProperty('root');
        $reflection_root->setAccessible(true);
        $reflection_root->setValue($n, $mockRoot);

        $this->assertSame('unit', $n->getTitle());
    }
}
