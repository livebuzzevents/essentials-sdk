<?php

namespace Tests;

use Buzz\EssentialsSdk\Cast;

class CastTest extends TestCase
{
    /** @test */
    public function test_copies_settings()
    {
        $class = new Example();
        $class->expand(['parent']);
        $class->options(['key' => 'value']);

        $new = Cast::single($class, ['email' => 'jordan_dobrev']);

        $this->assertEquals($class->getExpand(), $new->getExpand());
        $this->assertEquals($class->getOptions(), $new->getOptions());
    }
}
