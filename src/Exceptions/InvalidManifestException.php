<?php

namespace Pixo\Showcase\Exceptions;

class InvalidManifestException extends \DomainException
{
    public function __construct($path, $code = 0, \Exception $previous = null)
    {
        parent::__construct("Invalid manifest: $path", $code, $previous);
    }
}
