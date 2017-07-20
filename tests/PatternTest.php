<?php

namespace Pixo\Showcase;

class PatternTest extends \PHPUnit_Framework_TestCase
{
    public static function getMockJson()
    {
        return [
            'id' => 'pattern-id',
            'mockups' => [
                MockupTest::getMockJson(),
            ]
        ];
    }

    public function testFromJson()
    {
        $dir = '/path/to/file';
        $json = self::getMockJson();
        $pattern = Pattern::fromJson($json, $dir);
        $this->assertInstanceOf(Pattern::class, $pattern);

        $this->assertEquals($json['id'], $pattern->getId());

        $this->assertEquals(count($json['mockups']), count($pattern->getMockups()));
        foreach ($pattern->getMockups() as $mockup) {
            $this->assertInstanceOf(Mockup::class, $mockup);
        }
    }

    public function testJsonSerialize()
    {
        $dir = '/path/to/file';
        $source = self::getMockJson();
        $pattern = Pattern::fromJson($source, $dir);
        $json = $pattern->jsonSerialize();

        $this->assertArrayHasKey('id', $json);
        $this->assertEquals($source['id'], $json['id']);
        $this->assertArrayHasKey('mockups', $json);
        $this->assertEquals(count($source['mockups']), count($json['mockups']));
    }
}
