<?php

namespace Jasny\IteratorStream\Tests;

use Jasny\IteratorStream\AbstractOutputStream;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\IteratorStream\AbstractOutputStream
 */
class AbstractOutputStreamTest extends TestCase
{
    public function invalidStreamProvider()
    {
        $gd = imagecreate(1, 1);
        $readOnly = fopen('data://text/plain,hello', 'r');

        $closed = fopen('php://memory', 'w+');
        fclose($closed);

        return [
            ['foo', \InvalidArgumentException::class, "Invalid URI 'foo'; scheme missing"],
            [new \DateTime(), \TypeError::class, "Expected stream resource or uri (string), DateTime object given"],
            [$gd, \TypeError::class, "Expected stream resource or uri (string), gd resource given"],
            [$readOnly, \InvalidArgumentException::class, 'Stream "data://text/plain,hello" is not writable'],
            [$closed, \TypeError::class, "Expected stream resource or uri (string), resource (closed) given"]
        ];
    }

    /**
     * @dataProvider invalidStreamProvider
     */
    public function testInvalidStream($resource, $exception, $message)
    {
        $this->expectException($exception);
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


    public function testOpenStream()
    {
        vfsStream::setup();

        /** @var AbstractOutputStream|MockObject $stream */
        $stream = $this->getMockForAbstractClass(AbstractOutputStream::class, ['vfs://root/test.csv']);

        $handler = $stream->detach();
        $this->assertInternalType('resource', $handler);

        $this->assertEquals('stream', get_resource_type($handler));

        $meta = stream_get_meta_data($handler);
        $this->assertEquals('vfs://root/test.csv', $meta['uri']);
        $this->assertEquals('w', $meta['mode']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage fopen(vfs://root/foo/test.csv): failed to open stream
     */
    public function testOpenStreamFailed()
    {
        vfsStream::setup();

        /** @var AbstractOutputStream|MockObject $stream */
        $this->getMockForAbstractClass(AbstractOutputStream::class, ['vfs://root/foo/test.csv']);
    }
}
