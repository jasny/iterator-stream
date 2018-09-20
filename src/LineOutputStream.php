<?php

declare(strict_types=1);

namespace Jasny\IteratorStream;

/**
 * Write to a stream line by line.
 */
class LineOutputStream extends AbstractOutputStream
{
    /**
     * @var string Character to end a line.
     */
    protected $endline;

    /**
     * Class constructor.
     *
     * @param resource $stream
     * @param string   $endline
     */
    public function __construct($stream, string $endline = "\n")
    {
        parent::__construct($stream);

        $this->endline = $endline;
    }

    /**
     * Write an element to the stream.
     *
     * @param mixed $element
     * @return void
     */
    protected function writeElement($element): void
    {
        fwrite($this->stream, (string)$element . $this->endline);
    }
}
