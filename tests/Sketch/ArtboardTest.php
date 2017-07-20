<?php

namespace Pixo\Showcase\Sketch;

class ArtboardTest extends \PHPUnit_Framework_TestCase
{
    public static function getMockJson()
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

    public static function getMockPageJson()
    {
        return [
            'name' => 'Name of page',
        ];
    }

    public function testFromDocumentJson()
    {
        $description = 'Description of artboard';
        $pattern = 'pattern-id';
        $group = 'Name of group';
        $extra = 'Extra information';
        $json = [
            'id' => 'artboard-id',
            'name' => "{$group}/{$description} @{$pattern} {$extra}",
            'page' => self::getMockPageJson(),
            'rect' => [
                'width' => 800,
                'height' => 600,
            ],
        ];
        $page = self::getMockPageJson();
        $artboard = Artboard::fromDocumentJson($json, $page);
        $this->assertInstanceOf(Artboard::class, $artboard);
        $this->assertEquals($json['id'], $artboard->getId());
        $this->assertEquals($json['name'], $artboard->getName());
        $this->assertEquals($page['name'], $artboard->getPage());
        $this->assertEquals($description, $artboard->getDescription());
        $this->assertEquals($pattern, $artboard->getPattern());
        $this->assertEquals($group, $artboard->getGroup());
        $this->assertEquals($extra, $artboard->getExtra());
        $this->assertEquals($json['rect']['width'], $artboard->getWidth());
        $this->assertEquals($json['rect']['height'], $artboard->getHeight());
    }

    public function testFromJson()
    {
        $json = self::getMockJson();
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
        $source = self::getMockJson();
        $artboard = Artboard::fromJson($source);
        $json = $artboard->jsonSerialize();
        $this->assertEquals($source, $json);
    }
}
