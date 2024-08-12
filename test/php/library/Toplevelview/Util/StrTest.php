<?php

namespace Tests\Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\Util\Str;
use PHPUnit\Framework\TestCase;

final class StrTest extends TestCase
{
    public function testLimitWithSmallerString()
    {
        $this->assertSame('', Str::limit(null));
        $this->assertSame('', Str::limit(''));
        $this->assertSame('noop', Str::limit('noop'));
    }

    public function testLimitWithLongerStringAndSpecificLimit()
    {
        $this->assertSame(
            'Lorem ipsu...',
            Str::limit('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 10)
        );
        $this->assertSame(
            '🍔🍔🍔🍔🍔...',
            Str::limit('🍔🍔🍔🍔🍔🍔🍔🍔', 10)
        );
    }

    public function testLimitWithLongerStringAndSpecificEnd()
    {
        $this->assertSame(
            'L (...)',
            Str::limit('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 1, ' (...)')
        );
        $this->assertSame(
            'К (...)',
            Str::limit('Кто это читает, тот дурак', 1, ' (...)')
        );
        $this->assertSame(
            'К (🦔🦔🦔)',
            Str::limit('Кто это читает, тот дурак', 1, ' (🦔🦔🦔)')
        );
    }
}
