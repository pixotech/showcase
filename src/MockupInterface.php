<?php

namespace Pixo\Showcase;

use Pixo\Showcase\Sketch\ArtboardInterface;

interface MockupInterface
{
    /**
     * @return ArtboardInterface
     */
    public function getArtboard();

    /**
     * @return ImageInterface[]
     */
    public function getImages();
}
