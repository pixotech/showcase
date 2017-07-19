<?php

namespace Pixo\Showcase\Sketch;

interface ArtboardInterface
{
    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getExtra();

    /**
     * @return string
     */
    public function getGroup();

    /**
     * @return int
     */
    public function getHeight();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getPage();

    /**
     * @return string
     */
    public function getPattern();

    /**
     * @return int
     */
    public function getWidth();

    /**
     * @return bool
     */
    public function isPattern();
}
