<?php

namespace Jasny\IteratorStream\Tests;

use Jasny\IteratorStream\AbstractOutputStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\IteratorStream\AbstractOutputStream
 */
class AbstractOutputStreamTest extends TestCase
{
    public function invalidStreamProvider()
    {
        $closed = fopen('php://memory', 'w+');
        fclose($closed);

        return [
            ['foo', "Expected resource, string given"],
            [new \DateTime(), "Expected resource, DateTime object given"],
            [imagecreate(1, 1), "Expected resource to be a stream, gd resource given"],
            [fopen('data://text/plain,hello', 'r'), 'Stream "data://text/plain,hello" is not writable'],
            [$closed, "Expected resource, unknown type given"]
        ];
    }

    /**
     * @dataProvider invalidStreamProvider
     */
    public function testInvalidStream($resource, $message)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        /** @var AbstractOutputStream|MockObject $stream */
        $this->getMockForAbstractClass(AbstractOutputStream::class, [$resource]);
    }


    public function testDetach()
    {
        $resource = fopen('php://memory', 'w+');

        /** @var AbstractOutputStream|MockObject $stream */
        $stream = $this->getMockForAbstractClass(AbstractOutputStream::class, [$resource]);

        $detached = $stream->detach();

        $this->assertSame($resource, $detached);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Stream is not attached
     */
    public function testDetachTwice()
    {
        $resource = fopen('php://memory', 'w+');

        /** @var AbstractOutputStream|MockObject $stream */
        $stream = $this->getMockForAbstractClass(AbstractOutputStream::class, [$resource]);

        $stream->detach();
        $stream->detach();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Stream is not attached
     */
    public function testDetachWrite()
    {
        $resource = fopen('php://memory', 'w+');

        /** @var AbstractOutputStream|MockObject $stream */
        $stream = $this->getMockForAbstractClass(AbstractOutputStream::class, [$resource]);

        $stream->detach();
        $stream->write(new \EmptyIterator());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Stream has closed unexpectedly
     */
    public function testCloseWrite()
    {
        $resource = fopen('php://memory', 'w+');

        /** @var AbstractOutputStream|MockObject $stream */
        $stream = $this->getMockForAbstractClass(AbstractOutputStream::class, [$resource]);

        fclose($resource);
        $stream->write(new \EmptyIterator());
    }
}
