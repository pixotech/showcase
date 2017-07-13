<?php

namespace Pixo\Design\SketchPatterns;

class Export implements \Countable, \IteratorAggregate
{
    protected $images = [];

    protected $path;

    protected $source;

    public function __construct(ExportableInterface $source, $path, array $output)
    {
        $this->source = $source;
        $this->path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->parseOutput($output);
    }

    public function count()
    {
        return count($this->images);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->images);
    }

    protected function getSourceId()
    {
        return $this->source->getId();
    }

    protected function parseOutput(array $output)
    {
        $pattern = '/' . preg_quote($this->getSourceId()) . '(@[.0-9]+x)?\.([a-z]+)$/';
        foreach ($output as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $this->images[] = [
                    'path' => $this->path . $matches[0],
                    'scale' => $matches[1],
                    'format' => $matches[2],
                ];
            }
        }
    }
}
