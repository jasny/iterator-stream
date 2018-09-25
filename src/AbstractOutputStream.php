<?php

declare(strict_types=1);

namespace Jasny\IteratorStream;

use function Jasny\expect_type;

/**
 * Baste class for output stream
 */
abstract class AbstractOutputStream implements OutputStreamInterface
{
    /**
     * @var resource|null
     */
    protected $stream;


    /**
     * AbstractOutputStream constructor.
     *
     * @param resource|string|null $stream
     */
    public function __construct($stream)
    {
        $this->stream = isset($stream) ? $this->initStream($stream) : null;
    }


    /**
     * Initialize a stream
     *
     * @param resource|string $stream
     * @return resource
     * @throws \RuntimeException if stream failed to open
     * @throws \InvalidArgumentException if stream is not writable
     */
    protected function initStream($stream)
    {
        expect_type($stream, ['stream resource', 'string'], \TypeError::class,
            "Expected stream resource or uri (string), %s given");

        if (is_string($stream)) {
            $stream = $this->openStream($stream);
        }

        $meta = stream_get_meta_data($stream);

        if ($meta['mode'] === 'r') {
            throw new \InvalidArgumentException("Stream \"{$meta['uri']}\" is not writable");
        }

        return $stream;
    }

    /**
     * Open a stream
     *
     * @param string $uri
     * @return resource
     * @throws \RuntimeException if stream failed to open
     */
    protected function openStream(string $uri)
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);

        if (!isset($scheme) || $scheme === '') {
            throw new \InvalidArgumentException("Invalid URI '$uri'; scheme missing");
        }

        $reporting = error_reporting(error_reporting() & ~(E_WARNING | E_NOTICE));
        try {
            $stream = fopen($uri, 'w'); // Just in case stream wrapper throws an exception
        } finally {
            error_reporting($reporting);
        }

        if (!is_resource($stream)) {
            ['message' => $message] = error_get_last() ?? ['message' => "Failed to open '$uri'"];
            throw new \RuntimeException($message);
        }

        return $stream;
    }


    /**
     * Assert that a stream is attached.
     *
     * @return void
     * @throws \BadMethodCallException
     */
    protected function assertAttached(): void
    {
        if (!isset($this->stream)) {
            throw new \BadMethodCallException("Stream is not attached");
        }

        if (!is_resource($this->stream)) {
            throw new \RuntimeException("Stream has closed unexpectedly");
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
     * @param iterable $data
     * @return void
     */
    public function write(iterable $data): void
    {
        $this->assertAttached();

        $this->begin();

        foreach ($data as $element) {
            $this->writeElement($element);
        }

        $this->end();
    }


    /**
     * Detach the stream resource.
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

    /**
     * Create a new copy of the output stream.
     *
     * @param resource|string $stream
     * @return static
     */
    public function withStream($stream): self
    {
        $clone = clone $this;
        $clone->stream = $clone->initStream($stream);

        return $clone;
    }
}
