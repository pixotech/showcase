<?php

namespace Pixo\Showcase;

use Pixo\Showcase\Sketch\Artboard;
use Pixo\Showcase\Sketch\ArtboardTest;

class MockupTest extends \PHPUnit_Framework_TestCase
{
    public static function getMockJson()
    {
        return [
            'artboard' => ArtboardTest::getMockJson(),
            'images' => [
                [
                    'path' => '/path/to/image/png',
                ]
            ],
        ];
    }

    public function testFromJson()
    {
        $json = self::getMockJson();
        $mockup = Mockup::fromJson($json);
        $this->assertInstanceOf(Mockup::class, $mockup);

        $this->assertInstanceOf(Artboard::class, $mockup->getArtboard());
        $this->assertEquals($json['artboard'], $mockup->getArtboard()->jsonSerialize());

        $this->assertEquals(count($json['images']), count($mockup->getImages()));
        foreach ($mockup->getImages() as $image) {
            $this->assertInstanceOf(Image::class, $image);
        }
    }

    public function testJsonSerialize()
    {
        $source = self::getMockJson();
        $mockup = Mockup::fromJson($source);
        $json = $mockup->jsonSerialize();

        $this->assertArrayHasKey('artboard', $json);
        $this->assertArrayHasKey('images', $json);
        $this->assertEquals(count($source['images']), count($json['images']));
    }
}
