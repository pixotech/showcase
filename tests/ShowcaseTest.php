<?php

namespace Pixo\Showcase;

use Pixo\Showcase\Sketch\Application;

class ShowcaseTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadShowcase()
    {
        $source = 'test.sketch';
        $applicationName = 'fake-sketch';
        $applicationVersion = '1.2.3';
        $time = new \DateTime('-4 hours ago');

        $path = $this->createJsonFile([
            'source' => $source,
            'application' => [
                'name' => $applicationName,
                'version' => $applicationVersion,
            ],
            'patterns' => [
                [
                    'id' => 'pattern-one',
                    'mockups' => [
                        [],
                        [],
                    ],
                ],
            ],
            'time' => $time->format('c'),
        ]);

        $showcase = Showcase::load($path);
        $this->assertInstanceOf(Showcase::class, $showcase);
        $this->assertEquals($source, $showcase->getSource());

        $application = $showcase->getApplication();
        $this->assertInstanceOf(Application::class, $application);
        $this->assertEquals($applicationName, $application->getName());
        $this->assertEquals($applicationVersion, $application->getVersion());

        $this->assertInstanceOf(\DateTime::class, $showcase->getTime());
        $this->assertEquals($time, $showcase->getTime());

        $this->assertTrue($showcase->hasPattern('pattern-one'));
        $this->assertInstanceOf(Pattern::class, $showcase->getPattern('pattern-one'));
        $this->assertFalse($showcase->hasPattern('pattern-two'));

        $pattern = $showcase->getPattern('pattern-one');
        $this->assertEquals(2, count($pattern->getMockups()));
    }

    protected function createJsonFile($data)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $tmpName = tempnam(sys_get_temp_dir(), 'showcase-test-');
        file_put_contents($tmpName, $json);
        return $tmpName;
    }
}
