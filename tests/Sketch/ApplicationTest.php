<?php

namespace Pixo\Showcase\Sketch;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public static function getMockJson()
    {
        return [
            'name' => 'fake-sketch',
            'version' => '1.2.3',
        ];
    }

    public function testFromDocumentJson()
    {
        $json = [
            'app' => 'fake-sketch',
            'appVersion' => '1.2.3',
        ];
        $application = Application::fromDocumentMetadata($json);
        $this->assertInstanceOf(Application::class, $application);
        $this->assertEquals($json['app'], $application->getName());
        $this->assertEquals($json['appVersion'], $application->getVersion());
    }

    public function testFromJson()
    {
        $json = static::getMockJson();
        $application = Application::fromJson($json);
        $this->assertInstanceOf(Application::class, $application);
        $this->assertEquals($json['name'], $application->getName());
        $this->assertEquals($json['version'], $application->getVersion());
    }

    public function testJsonSerialize()
    {
        $source = static::getMockJson();
        $application = Application::fromJson($source);
        $json = $application->jsonSerialize();
        $this->assertArrayHasKey('name', $json);
        $this->assertEquals($source['name'], $json['name']);
        $this->assertArrayHasKey('version', $json);
        $this->assertEquals($source['version'], $json['version']);
    }
}
