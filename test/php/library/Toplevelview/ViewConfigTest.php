<?php

namespace Tests\Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\ViewConfig;
use Icinga\Module\Toplevelview\Model\View;

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

    public function testViewConfigDelete()
    {
        $c = new ViewConfig('test/testdata');
        $v = new View('deleteme', 'yml');

        // Generate the file
        $c->storeToFile($v);
        $this->assertFileExists('test/testdata/views/deleteme.yml');
        // Generate the backup dir
        $c->storeToFile($v);
        $this->assertDirectoryExists('test/testdata/views/deleteme');
        // Delete the file
        $c->delete($v);
        $this->assertFalse(file_exists('test/testdata/views/deleteme.yml'));

        // Remove generated files afterwards
        array_map('unlink', array_filter((array) glob("test/testdata/views/deleteme/*")));
        rmdir('test/testdata/views/deleteme/');
    }

    public function testViewConfigLoadAll()
    {
        $c = new ViewConfig('test/testdata');
        $views = $c->loadAll();

        $this->assertArrayHasKey('example', $views);
    }

    public function testViewConfigWithNoError()
    {
        $c = new ViewConfig('test/testdata');
        $view = $c->loadByName('example');

        $this->assertStringContainsString('linux-servers', $view->getText());
        $this->assertSame('5fc0ad55066b871d376eee60c84300d32ac7cb1d', $view->getTextChecksum());
        $this->assertSame('yml', $view->getFormat());

        $this->assertSame('example', $view->getName());
        $this->assertSame(['name' => 'My View'], $view->getMetaData());

        $clone = clone $view;
        $this->assertSame(null, $clone->getName());
        $this->assertFalse($clone->hasBeenLoaded());
    }

    public function testViewConfigWithTreeWithError()
    {
        $this->expectException(NotFoundError::class);

        $c = new ViewConfig('test/testdata');
        $view = $c->loadByName('example');

        $t = $view->getTree();
        $t->getById('0-1-2');
    }

    public function testViewConfigWithSession()
    {
        $c = new ViewConfig('test/testdata');
        $view = $c->loadByName('example');

        $view->setMeta('foo', 'bar');
        $this->assertSame('bar', $view->getMeta('foo'));
        $this->assertSame(null, $view->getMeta('bar'));
        $this->assertSame(['foo' => 'bar'], $view->getMetaData());

        $c->storeToSession($view);
        $view2 = $c->loadByName('example');

        $this->assertSame($view->getTextChecksum(), $view2->getTextChecksum());
        $this->assertFalse($view->hasBeenLoadedFromSession());
        $this->assertTrue($view2->hasBeenLoadedFromSession());
    }
}
