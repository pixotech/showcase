<?php

namespace Pixo\Showcase\Sketch\Exceptions;

class ExportException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Export failed");
    }
}
