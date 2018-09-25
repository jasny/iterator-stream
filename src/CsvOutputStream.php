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
     * @param resource|string|null $stream
     * @param string               $delimiter   Field delimiter (one character only)
     * @param string               $enclosure   Field enclosure (one character only)
     * @param string               $escapeChar  Escape character (one character only)
     */
    public function __construct(
        $stream = null,
        string $delimiter = ',',
        string $enclosure = '"',
        string $escapeChar = '\\'
    ) {
        parent::__construct($stream);

        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escapeChar = $escapeChar;
    }


    /**
     * Begin writing to stream.
     *
     * @param string[]|null $headers
     * @return void
     */
    protected function begin(?array $headers = null): void
    {
        if (isset($headers)) {
            $this->writeElement($headers);
        }
    }

    /**
     * Write an element to the stream.
     *
     * @param string[]|mixed $element
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

    /**
     * Write to traversable data to stream.
     *
     * @param iterable      $data
     * @param string[]|null $headers
     * @return void
     */
    public function write(iterable $data, ?array $headers = null): void
    {
        $this->assertAttached();

        $this->begin($headers);

        foreach ($data as $element) {
            $this->writeElement($element);
        }

        $this->end();
    }
}
