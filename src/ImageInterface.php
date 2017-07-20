<?php

namespace Pixo\Showcase;

interface ImageInterface
{
    /**
     * @return \SplFileInfo
     */
    public function getFile();

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

    /**
     * @return string
     */
    public function getSource();
}
