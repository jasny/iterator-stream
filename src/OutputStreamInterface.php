<?php

declare(strict_types=1);

namespace Jasny\IteratorStream;

/**
 * Traverse to elements and write to stream.
 */
interface OutputStreamInterface
{
    /**
     * Write to output stream.
     *
     * @param \Traversable $data
     * @return void
     */
    public function write(\Traversable $data): void;

    /**
     * Detach the stream.
     * The object is no longer usable.
     *
     * @return resource
     * @throws \LogicException if stream is already detached
     */
    public function detach();

    /**
     * Create a new copy of the output stream.
     *
     * @param resource|string $stream
     * @return static
     */
    public function withStream($stream);
}
