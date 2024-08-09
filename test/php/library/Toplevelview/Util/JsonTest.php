<?php

namespace Tests\Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\Util\Json;

use Icinga\Exception\Json\JsonEncodeException;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{
    public function testEncodeWithNoError()
    {
        $a = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];

        $this->assertSame('{"a":1,"b":2,"c":3,"d":4,"e":5}', Json::encode($a));
    }

    public function testEncodeWithError()
    {
        $this->expectException(JsonEncodeException::class);

        $a = "\xB1\x31";

        Json::encode($a);
    }
}
