<?php

namespace Pixo\Showcase\Sketch;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    public function testDocumentPath()
    {
        $path = tempnam(sys_get_temp_dir(), 'showcase-test-');
        file_put_contents($path, '');
        $document = new Document($path);
        $this->assertEquals($path, $document->getPath());
    }

    /**
     * @expectedException \Pixo\Showcase\Sketch\Exceptions\InvalidDocumentPathException
     */
    public function testCantCreateDocumentWithMissingFile()
    {
        $fakePath = '/this/is/not/a/real/filepath';
        $this->assertFalse(file_exists($fakePath));
        new Document($fakePath);
    }
}
