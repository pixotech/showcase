<?php

namespace Pixo\Showcase;

class Pattern implements PatternInterface, \JsonSerializable
{
    protected $id;

    protected $mockups = [];

    public static function fromJson(array $json, $directory)
    {
        $pattern = new static($json['id']);
        if (!empty($json['mockups'])) {
            foreach ($json['mockups'] as $mockup) {
                $pattern->addMockup(Mockup::fromJson($mockup, $directory));
            }
        }
        return $pattern;
    }

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function __debugInfo()
    {
        return $this->jsonSerialize();
    }

    public function __toString()
    {
        return "@{$this->getId()}";
    }

    public function addMockup(MockupInterface $mockup)
    {
        $this->mockups[] = $mockup;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMockups()
    {
        return $this->mockups;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'mockups' => $this->getMockups(),
        ];
    }
}
