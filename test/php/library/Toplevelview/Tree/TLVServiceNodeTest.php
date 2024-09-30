<?php

namespace Tests\Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\Tree\TLVServiceNode;

use PHPUnit\Framework\TestCase;
use stdClass;
use ReflectionClass;

final class TLVServiceNodeTest extends TestCase
{
    public function testGetTitle()
    {
        $n = new TLVServiceNode();
        $n->setProperties(['service'=>'unit', 'host'=>'test']);
        $this->assertSame('test!unit', $n->getKey());

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

        $this->assertSame('test: unit', $n->getTitle());
    }

    public function testGetStatus()
    {
        $n = new TLVServiceNode();
        $n->setProperties(['service'=>'unit', 'host'=>'test']);

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

        $this->assertSame('missing', $n->getStatus()->getOverall());
    }
}
