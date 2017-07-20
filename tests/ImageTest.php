<?php

namespace Pixo\Showcase;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    protected function getMockJson()
    {
        $id = $this->makeUuid();
        $format = 'png';
        return [
            'path' => "$id.$format",
            'source' => $id,
            'format' => $format,
            'scale' => 1,
        ];
    }

    public function testFromPath()
    {
        $source = strtoupper($this->makeUuid());
        $scale = 2;
        $format = 'png';
        $path = sprintf('%s@%0.0fx.%s', $source, $scale, $format);
        $image = Image::fromPath($path);
        $this->assertEquals($path, $image->getPath());
        $this->assertEquals($source, $image->getSource());
        $this->assertEquals($format, $image->getFormat());
        $this->assertEquals($scale, $image->getScale());
    }

    public function testFromPathOfUnscaledImage()
    {
        $source = strtoupper($this->makeUuid());
        $scale = 1;
        $format = 'png';
        $path = "{$source}.{$format}";
        $image = Image::fromPath($path);
        $this->assertEquals($path, $image->getPath());
        $this->assertEquals($source, $image->getSource());
        $this->assertEquals($format, $image->getFormat());
        $this->assertEquals($scale, $image->getScale());
    }

    public function testFromJson()
    {
        $json = self::getMockJson();
        $image = Image::fromJson($json);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals($json['path'], $image->getPath());
        $this->assertEquals($json['source'], $image->getSource());
        $this->assertEquals($json['format'], $image->getFormat());
        $this->assertEquals($json['scale'], $image->getScale());
    }

    public function testJsonSerialize()
    {
        $source = self::getMockJson();
        $image = Image::fromJson($source);
        $json = $image->jsonSerialize();
        $this->assertEquals($source, $json);
    }

    protected function makeUuid()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}
