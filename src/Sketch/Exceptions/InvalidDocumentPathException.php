<?php

namespace Pixo\Showcase\Sketch\Exceptions;

class InvalidDocumentPathException extends \InvalidArgumentException
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
        parent::__construct("Not a file: $path");
    }
}
