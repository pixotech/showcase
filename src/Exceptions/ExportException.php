<?php

namespace Pixo\Showcase\Exceptions;

class ExportException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Export failed");
    }
}
