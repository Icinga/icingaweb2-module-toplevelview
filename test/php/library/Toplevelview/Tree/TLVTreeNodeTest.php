<?php

namespace Tests\Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\Tree\TLVTreeNode;

use PHPUnit\Framework\TestCase;

final class TLVTreeNodeTest extends TestCase
{
    public function testGetTitle()
    {
        $n = new TLVTreeNode();

        $n->setProperties(['name' => 'bar']);
        $this->assertSame('bar', $n->getTitle());

        $this->assertSame(['name' => 'bar'], $n->getProperties());

        $n->set('name', 'foo');
        $this->assertSame('foo', $n->getTitle());
    }

    public function testGetBreadCrumb()
    {
        $n = new TLVTreeNode();
        $this->assertSame([$n], $n->getBreadCrumb());
    }
}
