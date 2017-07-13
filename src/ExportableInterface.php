<?php

namespace Pixo\Showcase;

interface ExportableInterface
{
    public function getHeight();

    public function getId();

    public function getPatternId();

    public function getWidth();

    public function isPattern();
}
