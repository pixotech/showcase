<?php

namespace Pixo\Design\SketchPatterns;

class Artboard extends Exportable implements ArtboardInterface
{
    protected $page;

    public function __construct(array $artboard, array $page)
    {
        parent::__construct($artboard);
        $this->page = $page['name'];
        $this->width = $artboard['rect']['width'];
        $this->height = $artboard['rect']['height'];
    }

    public function getPage()
    {
        return $this->page;
    }
}
