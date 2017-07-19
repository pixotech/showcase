<?php

namespace Pixo\Showcase;

interface ShowcaseInterface
{
    public function addPattern(PatternInterface $pattern);

    public function getPattern($id);

    public function getPatterns();

    /**
     * @return string
     */
    public function getSource();

    /**
     * @return \DateTime
     */
    public function getTime();

    public function hasPattern($id);
}
