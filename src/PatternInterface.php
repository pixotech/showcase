<?php

namespace Pixo\Showcase;

interface PatternInterface
{
    public function addMockup(MockupInterface $mockup);

    public function getId();

    /**
     * @return MockupInterface[]
     */
    public function getMockups();
}
