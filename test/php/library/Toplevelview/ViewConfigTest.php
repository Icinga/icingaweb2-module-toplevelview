<?php

namespace Tests\Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\ViewConfig;

use Icinga\Exception\NotReadableError;
use Icinga\Exception\NotFoundError;
use PHPUnit\Framework\TestCase;

final class ViewConfigTest extends TestCase
{
    public function testViewConfigWithNoSuchView()
    {
        $this->expectException(NotReadableError::class);

        $c = new ViewConfig('test/testdata');
        $view = $c->loadByName('nosuchview');
    }

    public function testViewConfigWithNoError()
    {
        $c = new ViewConfig('test/testdata');
        $view = $c->loadByName('example');

        $this->assertStringContainsString('linux-servers', $view->getText());
        $this->assertSame('5fc0ad55066b871d376eee60c84300d32ac7cb1d', $view->getTextChecksum());
        $this->assertSame('yml', $view->getFormat());
        $this->assertSame('example', $view->getName());

        $clone = clone $view;
        $this->assertSame(null, $clone->getName());
        $this->assertFalse($clone->hasBeenLoaded());
    }

    public function testViewConfigWithTree()
    {
        $this->expectException(NotFoundError::class);

        $c = new ViewConfig('test/testdata');
        $view = $c->loadByName('example');

        $t = $view->getTree();
        $t->getById('0-1-2');
    }
}
