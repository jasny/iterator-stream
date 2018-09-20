<?php

namespace Jasny\IteratorStream\Tests;

use Jasny\IteratorStream\LineOutputStream;
use PHPStan\Testing\TestCase;

/**
 * @covers \Jasny\IteratorStream\AbstractOutputStream
 * @covers \Jasny\IteratorStream\LineOutputStream
 */
class LineOutputStreamTest extends TestCase
{
    public function writeProvider()
    {
        return [
            [
                ['hello world', 'lovely day'],
                "hello world\nlovely day\n"
            ],
            [
                ['one', null, 3, true, false],
                "one\n\n3\n1\n\n"
            ],
            [
                [],
                ""
            ]
        ];
    }

    /**
     * @dataProvider writeProvider
     */
    public function testWrite(array $values, string $expected)
    {
        $resource = fopen('php://memory', 'w+');
        $iterator = new \ArrayIterator($values);

        $stream = new LineOutputStream($resource);
        $stream->write($iterator);

        fseek($resource, 0);
        $result = fread($resource, 1024);

        $this->assertEquals($expected, $result);
    }

    public function testWriteEndline()
    {
        $resource = fopen('php://memory', 'w+');
        $iterator = new \ArrayIterator(['hello world', 'lovely day']);

        $stream = new LineOutputStream($resource, '.');
        $stream->write($iterator);

        fseek($resource, 0);
        $result = fread($resource, 1024);

        $this->assertEquals("hello world.lovely day.", $result);
    }
}
