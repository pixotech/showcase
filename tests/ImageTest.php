<?php

namespace Pixo\Showcase;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    public function testFromJson()
    {
        $json = $this->getImageData();
        $image = Image::fromJson($json);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals($json['path'], $image->getPath());
        $this->assertEquals($json['format'], $image->getFormat());
        $this->assertEquals($json['scale'], $image->getScale());
    }

    public function testJsonSerialize()
    {
        $source = $this->getImageData();
        $image = Image::fromJson($source);
        $json = $image->jsonSerialize();
        $this->assertEquals($source, $json);
    }

    protected function getImageData()
    {
        return [
            'path' => 'path-to-image.png',
            'format' => 'png',
            'scale' => 1,
        ];
    }
}
