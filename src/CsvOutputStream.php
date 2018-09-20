<?php

declare(strict_types=1);

namespace Jasny\IteratorStream;

/**
 * Write to a stream as CSV.
 * @see fputcsv
 */
class CsvOutputStream extends AbstractOutputStream
{
    /**
     * @var string[]|null  CSV headers
     */
    protected $headers;

    /**
     * @var string  (one character only)
     */
    protected $delimiter;

    /**
     * @var string  (one character only)
     */
    protected $enclosure;

    /**
     * @var string  (one character only)
     */
    protected $escapeChar;


    /**
     * Class constructor.
     *
     * @param resource      $stream
     * @param string[]|null $headers
     * @param string        $delimiter   Field delimiter (one character only)
     * @param string        $enclosure   Field enclosure (one character only)
     * @param string        $escapeChar  Escape character (one character only)
     */
    public function __construct(
        $stream,
        ?array $headers = null,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escapeChar = '\\'
    ) {
        parent::__construct($stream);

        $this->headers = $headers;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escapeChar = $escapeChar;
    }


    /**
     * Begin writing to stream.
     *
     * @return void
     */
    protected function begin(): void
    {
        if (isset($this->headers)) {
            fputcsv($this->stream, $this->headers, $this->delimiter, $this->enclosure, $this->escapeChar);
        }
    }


    /**
     * Write an element to the stream.
     *
     * @param array $element
     * @return void
     */
    protected function writeElement($element): void
    {
        if (!is_array($element)) {
            $type = (is_object($element) ? get_class($element) . ' ' : '') . gettype($element);
            trigger_error("Unable to write to element to CSV stream; expect array, $type given", E_USER_WARNING);

            fwrite($this->stream, "\n");

            return;
        }

        fputcsv($this->stream, $element, $this->delimiter, $this->enclosure, $this->escapeChar);
    }
}
