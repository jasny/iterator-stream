Jasny Iterator Stream
===

[![Build Status](https://travis-ci.org/jasny/iterator-stream.svg?branch=master)](https://travis-ci.org/jasny/iterator-stream)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/iterator-stream/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/iterator-stream/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/iterator-stream/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/iterator-stream/?branch=master)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/iterator-stream.svg)](https://packagist.org/packages/jasny/iterator-stream)
[![Packagist License](https://img.shields.io/packagist/l/jasny/iterator-stream.svg)](https://packagist.org/packages/jasny/iterator-stream)

Output streams for iterators, formatting and writing one element at a time.

_If you're looking for a traversable input stream, take a look at [`SplFileObject`](http://php.net/SplFileObject)._

Installation
---

    composer require jasny/iterator-stream

Usage
---

```php
$handler = fopen('path/to/some/file.txt');
$stream = new LineOutputStream($handler);

$iterator = new \ArrayIterator(['hello, 'sweet', 'world']);

$stream->write($iterator);
```

All stream objects take a stream resource as first parameter of the constructor.

To use it with a [PSR-7 streams](https://www.php-fig.org/psr/psr-7/#13-streams), you need to detach the underlying
resource and pass it to constructor.

```php
$handler = $psr7stream->detach();
$stream = new LineOutputStream($handler);
```

Streams
---

* [Line](#line)
* [CSV](#csv)
* [JSON](#json)

### Line

Write to a stream line by line.

```php
LineOutputStream(resource $stream, string $endLine = "\n")
```

### CSV

Write to a stream as CSV. See [`fputcsv()`](https://php.net/fputcsv).

```php
CsvOutputStream(
    resource $stream,
    array|null $headers = ['name', 'age', 'email'],
    string $delimiter = ',',
    string $enclosure = '"',
    string $escapeChar = '\\'
)
```

Headers may be specified, which will be written as first line. 

Each element of the iterator MUST be an array with a fixed number of values. Keys are ignored, if the elements are
associative arrays or objects, use the [`ProjectionIterator`](https://github.com/jasny/iterator#projectioniterator) from
the jasny\iterator library.

### JSON

Write to a stream as JSON array. See [`json_encode`](https://php.net/json_encode).

```php
JsonOutputStream(resource $stream, int $options = 0)
```

Options may be provided as binary set using the `JSON_*` constants.

You can add `JsonOutputStream::OUTPUT_LINES` as option, in which case each element is outputed as line, without turning
the complete output into a JSON array. 

```php
JsonOutputStream(resource $stream, int $options = \JSON_PRETTY_PRINT | JsonOutputStream::OUTPUT_LINES);
```
