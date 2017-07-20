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
        $dir = '/path/to/file';
        $path = sprintf('%s@%0.0fx.%s', $source, $scale, $format);
        $image = Image::fromPath($path, $dir);
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
        $dir = '/path/to/file';
        $path = "{$source}.{$format}";
        $image = Image::fromPath($path, $dir);
        $this->assertEquals($path, $image->getPath());
        $this->assertEquals($source, $image->getSource());
        $this->assertEquals($format, $image->getFormat());
        $this->assertEquals($scale, $image->getScale());
    }

    public function testFromJson()
    {
        $dir = '/path/to/file';
        $json = self::getMockJson();
        $image = Image::fromJson($json, $dir);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertEquals($json['path'], $image->getPath());
        $this->assertEquals($json['source'], $image->getSource());
        $this->assertEquals($json['format'], $image->getFormat());
        $this->assertEquals($json['scale'], $image->getScale());
    }

    public function testGetFile()
    {
        $source = strtoupper($this->makeUuid());
        $format = 'png';
        $dir = '/path/to/file';
        $path = "{$source}.{$format}";
        $image = Image::fromPath($path, $dir);

        $file = $image->getFile();
        $this->assertInstanceOf(\SplFileInfo::class, $file);
        $this->assertEquals($dir . DIRECTORY_SEPARATOR . $path, $file->getPathname());
    }

    public function testJsonSerialize()
    {
        $dir = '/path/to/file';
        $source = self::getMockJson();
        $image = Image::fromJson($source, $dir);
        $json = $image->jsonSerialize();
        $this->assertEquals($source, $json);
    }

    protected function makeUuid()
    {
        // from here, I think: http://us1.php.net/manual/en/function.uniqid.php#94959
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
