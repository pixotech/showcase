<?php

namespace Pixo\Showcase\Sketch;

interface ExportableInterface
{
    public function getHeight();

    public function getId();

    public function getName();

    public function getPage();

    public function getPatternId();

    public function getWidth();

    public function isPattern();
}
