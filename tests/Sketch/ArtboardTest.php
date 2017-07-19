<?php

namespace Pixo\Showcase\Sketch;

class ArtboardTest extends \PHPUnit_Framework_TestCase
{
    public function testFromJson()
    {
        $json = $this->getArtboardData();
        $artboard = Artboard::fromJson($json);
        $this->assertInstanceOf(Artboard::class, $artboard);
        $this->assertEquals($json['id'], $artboard->getId());
        $this->assertEquals($json['name'], $artboard->getName());
        $this->assertEquals($json['page'], $artboard->getPage());
        $this->assertEquals($json['description'], $artboard->getDescription());
        $this->assertEquals($json['pattern'], $artboard->getPattern());
        $this->assertEquals($json['group'], $artboard->getGroup());
        $this->assertEquals($json['extra'], $artboard->getExtra());
        $this->assertEquals($json['width'], $artboard->getWidth());
        $this->assertEquals($json['height'], $artboard->getHeight());
    }

    public function testJsonSerialize()
    {
        $source = $this->getArtboardData();
        $artboard = Artboard::fromJson($source);
        $json = $artboard->jsonSerialize();
        $this->assertEquals($source, $json);
    }

    protected function getArtboardData()
    {
        return [
            'id' => 'artboard-id',
            'name' => 'Name of artboard',
            'page' => 'Name of page',
            'description' => 'Description of artboard',
            'pattern' => 'pattern-id',
            'group' => 'Name of group',
            'extra' => 'Extra information',
            'width' => 800,
            'height' => 600,
        ];
    }
}
