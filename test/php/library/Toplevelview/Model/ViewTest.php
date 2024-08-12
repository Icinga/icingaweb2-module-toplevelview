<?php

namespace Tests\Icinga\Module\Toplevelview;

use Icinga\Module\Toplevelview\Model\View;

use PHPUnit\Framework\TestCase;

final class ViewTest extends TestCase
{
    public function testViewValidateName()
    {
        $v = new View('myview', 'yml');
        $this->assertSame('myview', $v->getName());

        $tests = [
            'example' => true,
            'Example' => true,
            'ex_ample' => true,
            'ex_am-ple' => true,
            '1ex_am-ple2' => true,
            'Ex_a2m-ple123' => true,
            '1ex_am-ple2' => true,
            'example.yaml' => true,
            'e#x(_)am_123-ple' => false,
            'ex/ample' => false,
            'ex\ample' => false,
            '' => false,
            'Филе' => true,
            '😺' => true,
            '../../example' => false
        ];

        foreach ($tests as $name => $value) {
            $v->setName($name);
            $this->assertSame($v->validateName(), $value);
        }
    }
}
