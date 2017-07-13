<?php

namespace Pixo\Design\SketchPatterns;

class Artboard extends Exportable implements ArtboardInterface
{
    public function __construct(array $artboard)
    {
        parent::__construct($artboard);
        $this->width = $artboard['rect']['width'];
        $this->height = $artboard['rect']['height'];
    }
}
