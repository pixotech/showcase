<?php

namespace Pixo\Showcase\Exceptions;

class MissingManifestException extends \DomainException
{
    public function __construct($path, $code = 0, \Exception $previous = null)
    {
        parent::__construct("Missing manifest: $path", $code, $previous);
    }
}
