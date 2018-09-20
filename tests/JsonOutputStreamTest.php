<?php

namespace Jasny\IteratorStream\Tests;

use Jasny\IteratorStream\JsonOutputStream;
use PHPStan\Testing\TestCase;

/**
 * @covers \Jasny\IteratorStream\AbstractOutputStream
 * @covers \Jasny\IteratorStream\JsonOutputStream
 */
class JsonOutputStreamTest extends TestCase
{
    public function testWrite()
    {
        $values = [
            ['one' => 'uno', 'two' => 'dos'],
            (object)['name' => 'arnold', 'nick' => 'jasny'],
            null,
            42
        ];
        $iterator = new \ArrayIterator($values);

        $resource = fopen('php://memory', 'w+');

        $stream = new JsonOutputStream($resource);
        $stream->write($iterator);

        fseek($resource, 0);
        $result = fread($resource, 1024);

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString(json_encode($values), $result);
    }

    public function testWriteOptions()
    {
        $values = [
            ['one' => 'uno', 'two' => 'dos'],
            (object)['name' => 'arnold', 'nick' => 'jasny']
        ];
        $iterator = new \ArrayIterator($values);

        $resource = fopen('php://memory', 'w+');

        $stream = new JsonOutputStream($resource, JSON_PRETTY_PRINT);
        $stream->write($iterator);

        fseek($resource, 0);
        $result = fread($resource, 1024);

        $expected = json_encode($values, JSON_PRETTY_PRINT);

        $this->assertJson($result);
        $this->assertEquals($expected, $result);
    }
}
