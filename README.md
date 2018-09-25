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

$iterable = ['hello, 'sweet', 'world'];

$stream->write($iterator);
```

The `write()` takes an array or `Traversable` object (not just `Iterators`). 

All iterator stream objects take a stream resource or URI (string) as first parameter of the constructor.

```php
new LineOutputStream('php://output');
new CsvOutputStream('file://path/to/some/file.csv');
```

If an URI is passed, the iterator stream will open it using `fopen`. Using a scheme is required, also for regular
files.

To use it with [PSR-7 streams](https://www.php-fig.org/psr/psr-7/#13-streams), you need to detach the underlying
resource and pass it to constructor.

```php
$handler = $psr7stream->detach();
$stream = new LineOutputStream($handler);
```

### Prototype pattern

Iterator output streams support the prototype design pattern. They may be created detached of a stream resource.
The `withStream` creates a clone of the output stream with the given resource attached.

```php
$prototype = new JsonOutputStream();

$stream = $prototype->withStream($resource);
$stream->write($iterable);
``` 

Prototyping makes the library easier to use with dependency inversion in SOLID applictions. 


Output streams
---

* [Line](#line)
* [CSV](#csv)
* [JSON](#json)

### Line

Write to a stream line by line.

```php
LineOutputStream(resource|string $stream, string $endLine = "\n")
```

### CSV

Write to a stream as CSV. See [`fputcsv()`](https://php.net/fputcsv).

```php
CsvOutputStream(
    resource|string $stream,
    string $delimiter = ',',
    string $enclosure = '"',
    string $escapeChar = '\\'
)
```

The `write()` method optionally takes headers as a seconds argument. These are written as first line. 

```php
$output = new CsvOutputStream($stream);
$output->write($data, ['name', 'age', 'email']);
```

Each element of the iterable MUST be an array with a fixed number of values. Keys are ignored, if the elements are
associative arrays or objects, use [`iterable_project`](https://github.com/jasny/iterable-functions#iterable_project)
from `jasny\iterable-functions`.

### JSON

Write to a stream as JSON array. See [`json_encode`](https://php.net/json_encode).

```php
JsonOutputStream(resource|string $stream, int $options = 0)
```

Options may be provided as binary set using the `JSON_*` constants.

You can add `JsonOutputStream::OUTPUT_LINES` as option, in which case each element is outputed as line, without turning
the complete output into a JSON array. 

```php
$output = new JsonOutputStream($stream, \JSON_PRETTY_PRINT | JsonOutputStream::OUTPUT_LINES);
```
