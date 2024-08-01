<?php

namespace Tests\Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\ViewConfig;

use Icinga\Exception\NotReadableError;
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
    }
}
