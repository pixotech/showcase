<?php

namespace Pixo\Design\SketchPatterns;

interface ExportableInterface
{
    public function getHeight();

    public function getId();

    public function getPatternId();

    public function getWidth();

    public function isPattern();
}
