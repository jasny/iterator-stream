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

    public function writeProvider()
    {
        return [
            [$this->values],
            [new \ArrayIterator($this->values)]
        ];
    }

    /**
     * @dataProvider writeProvider
     */
    public function testWrite($data)
    {
        $resource = fopen('php://memory', 'w+');

        $stream = new CsvOutputStream($resource);
        $stream->write($data);

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

        $stream = new CsvOutputStream($resource, ";", "'");
        $stream->write($this->values);

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

        $stream = new CsvOutputStream($resource);
        $stream->write($this->values, ['order', 'lorem', 'color', 'integer']);

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

        $stream = new CsvOutputStream($resource);
        $stream->write(new \EmptyIterator(), ['order', 'lorem', 'color', 'integer']);

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

        $resource = fopen('php://memory', 'w+');

        $stream = new CsvOutputStream($resource);
        @$stream->write($values);

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


    public function testWithStream()
    {
        $prototype = new CsvOutputStream();

        $resource = fopen('php://memory', 'w+');
        $stream = $prototype->withStream($resource);

        $this->assertInstanceOf(CsvOutputStream::class, $stream);
        $this->assertNotSame($prototype, $stream);
        $this->assertAttributeSame($resource, 'stream', $stream);
    }

    /**
     * @depends testWithStream
     */
    public function testWithStreamOptions()
    {
        $prototype = new CsvOutputStream(null, ';', ',');

        $resource = fopen('php://memory', 'w+');
        $stream = $prototype->withStream($resource);

        $this->assertAttributeEquals(';', 'delimiter', $stream);
        $this->assertAttributeEquals(',', 'enclosure', $stream);
    }
}
