<?php

namespace Jasny\IteratorStream\Tests;

use Jasny\IteratorStream\CsvOutputStream;
use Jasny\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\IteratorStream\CsvOutputStream
 */
class CsvOutputStreamTest extends TestCase
{
    use TestHelper;

    protected $values = [
        ['one', 'foo', 'red', 22],
        ['two', 'bar', 'green', 42],
        ['three', 'qux,\'wuz;dux', 'blue', -20]
    ];

    public function testWrite()
    {
        $resource = fopen('php://memory', 'w+');
        $iterator = new \ArrayIterator($this->values);

        $stream = new CsvOutputStream($resource);
        $stream->write($iterator);

        fseek($resource, 0);
        $result = fread($resource, 1024);

        $expected = <<<CSV
one,foo,red,22
two,bar,green,42
three,"qux,'wuz;dux",blue,-20

CSV;

        $this->assertEquals($expected, $result);
    }

    public function testWriteOptions()
    {
        $resource = fopen('php://memory', 'w+');
        $iterator = new \ArrayIterator($this->values);

        $stream = new CsvOutputStream($resource, null, ";", "'");
        $stream->write($iterator);

        fseek($resource, 0);
        $result = fread($resource, 1024);

        $expected = <<<CSV
one;foo;red;22
two;bar;green;42
three;'qux,''wuz;dux';blue;-20

CSV;

        $this->assertEquals($expected, $result);
    }

    public function testWriteEmpty()
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new CsvOutputStream($resource);
        $stream->write(new \EmptyIterator());

        fseek($resource, 0);
        $result = fread($resource, 1024);

        $this->assertEquals("", $result);
    }

    public function testWriteHeaders()
    {
        $resource = fopen('php://memory', 'w+');
        $iterator = new \ArrayIterator($this->values);

        $stream = new CsvOutputStream($resource, ['order', 'lorem', 'color', 'integer']);
        $stream->write($iterator);

        fseek($resource, 0);
        $result = fread($resource, 1024);

        $expected = <<<CSV
order,lorem,color,integer
one,foo,red,22
two,bar,green,42
three,"qux,'wuz;dux",blue,-20

CSV;

        $this->assertEquals($expected, $result);
    }

    public function testWriteHeadersEmpty()
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new CsvOutputStream($resource, ['order', 'lorem', 'color', 'integer']);
        $stream->write(new \EmptyIterator());

        fseek($resource, 0);
        $result = fread($resource, 1024);

        $expected = <<<CSV
order,lorem,color,integer

CSV;

        $this->assertEquals($expected, $result);
    }

    public function testWriteInvalidElement()
    {
        $values = [
            ['one', 'foo', 'red', 22],
            'hello world',
            ['three', 'qux,\'wuz;dux', 'blue', -20]
        ];
        $iterator = new \ArrayIterator($values);

        $resource = fopen('php://memory', 'w+');

        $stream = new CsvOutputStream($resource);
        @$stream->write($iterator);

        $this->assertLastError(E_USER_WARNING, "Unable to write to element to CSV stream; " .
            "expect array, string given");

        fseek($resource, 0);
        $result = fread($resource, 1024);

        $expected = <<<CSV
one,foo,red,22

three,"qux,'wuz;dux",blue,-20

CSV;

        $this->assertEquals($expected, $result);
    }

}
