<?php

namespace Pixo\Showcase;

interface ImageInterface
{
    /**
     * @return string
     */
    public function getFormat();

    /**
     * @return string
     */
    public function getPath();

    /**
     * @return float
     */
    public function getScale();
}
