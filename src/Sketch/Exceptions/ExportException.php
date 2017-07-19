<?php

namespace Pixo\Showcase\Sketch\Exceptions;

use Pixo\Showcase\Sketch\ArtboardInterface;
use Pixo\Showcase\Sketch\DocumentInterface;

class ExportException extends \Exception
{
    public function __construct(DocumentInterface $doc, ArtboardInterface $artboard, $path, $format = null, $scale = null)
    {
        parent::__construct("Export failed");
    }
}
