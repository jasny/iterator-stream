<?php

declare(strict_types=1);

namespace Jasny\IteratorStream;

/**
 * Baste class for output stream
 */
abstract class AbstractOutputStream implements OutputStreamInterface
{
    /**
     * @var resource
     */
    protected $stream;

    /**
     * @var int  binary set
     */
    protected $options;

    /**
     * AbstractOutputStream constructor.
     *
     * @param resource $stream
     */
    public function __construct($stream)
    {
        $this->assertStreamResource($stream);

        $this->stream = $stream;
    }


    /**
     * Assert that a resource is a writable stream.
     *
     * @param resource $resource
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function assertStreamResource($resource): void
    {
        if (get_resource_type($resource) !== 'stream') {
            $type = (is_object($resource) ? get_class($resource) . ' ' : '') . gettype($resource);
            throw new \InvalidArgumentException("Expected resource to be a stream, $type given");
        }

        $meta = stream_get_meta_data($resource);

        if ($meta['mode'] === 'r') {
            throw new \InvalidArgumentException("Stream is not writable");
        }
    }

    /**
     * Assert that a stream is attached.
     *
     * @return void
     * @throws \LogicException
     */
    protected function assertAttached(): void
    {
        if (!isset($this->stream)) {
            throw new \LogicException("Stream is not attached");
        }
    }


    /**
     * Begin writing to the stream.
     *
     * @return void
     */
    protected function begin(): void
    {
    }

    /**
     * End writing to the stream.
     *
     * @return void
     */
    protected function end(): void
    {
    }

    /**
     * Write an element to the stream.
     *
     * @param mixed $element
     * @return void
     */
    abstract protected function writeElement($element): void;


    /**
     * Write to traversable data to stream.
     *
     * @param \Traversable $data
     * @return void
     */
    public function write(\Traversable $data): void
    {
        $this->assertAttached();

        $this->begin();

        foreach ($data as $element) {
            $this->writeElement($element);
        }

        $this->end();
    }


    /**
     * Detach the stream.
     * The object is no longer usable.
     *
     * @return resource
     * @throws \RuntimeException if stream is already detached
     */
    public function detach()
    {
        $this->assertAttached();

        $stream = $this->stream;
        $this->stream = null;

        return $stream;
    }
}
