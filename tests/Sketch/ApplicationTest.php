<?php

namespace Pixo\Showcase\Sketch;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testFromJson()
    {
        $json = [
            'name' => 'fake-sketch',
            'version' => '1.2.3',
        ];
        $application = Application::fromJson($json);
        $this->assertInstanceOf(Application::class, $application);
        $this->assertEquals($json['name'], $application->getName());
        $this->assertEquals($json['version'], $application->getVersion());
    }

    public function testJsonSerialize()
    {
        $source = [
            'name' => 'fake-sketch',
            'version' => '1.2.3',
        ];
        $application = Application::fromJson($source);
        $json = $application->jsonSerialize();
        $this->assertArrayHasKey('name', $json);
        $this->assertEquals($source['name'], $json['name']);
        $this->assertArrayHasKey('version', $json);
        $this->assertEquals($source['version'], $json['version']);
    }
}
