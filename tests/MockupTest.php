<?php

namespace Pixo\Showcase;

use Pixo\Showcase\Sketch\Artboard;

class MockupTest extends \PHPUnit_Framework_TestCase
{
    public function testFromJson()
    {
        $json = [
            'artboard' => [
                'id' => 'artboard-id',
            ],
            'images' => [
                [
                    'path' => '/path/to/image/png',
                ]
            ],
        ];
        $mockup = Mockup::fromJson($json);
        $this->assertInstanceOf(Mockup::class, $mockup);

        $this->assertInstanceOf(Artboard::class, $mockup->getArtboard());

        $this->assertEquals(count($json['images']), count($mockup->getImages()));
        foreach ($mockup->getImages() as $image) {
            $this->assertInstanceOf(Image::class, $image);
        }
    }

    public function testJsonSerialize()
    {
        $source = [
            'artboard' => [
                'id' => 'artboard-id',
            ],
            'images' => [
                [
                    'path' => '/path/to/image/png',
                ]
            ],
        ];
        $mockup = Mockup::fromJson($source);
        $json = $mockup->jsonSerialize();

        $this->assertArrayHasKey('artboard', $json);
        $this->assertArrayHasKey('images', $json);
        $this->assertEquals(count($source['images']), count($json['images']));
    }
}
