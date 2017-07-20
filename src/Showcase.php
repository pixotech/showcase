<?php

namespace Pixo\Showcase;

use Pixo\Showcase\Sketch\Application;
use Pixo\Showcase\Sketch\ApplicationInterface;
use Pixo\Showcase\Sketch\Document;
use Pixo\Showcase\Sketch\DocumentInterface;
use Pixo\Showcase\Sketch\Artboard;
use Pixo\Showcase\Sketch\ArtboardInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class Showcase implements ShowcaseInterface, \JsonSerializable
{
    protected $application;

    protected $patterns = [];

    protected $source;

    /**
     * @var \DateTime
     */
    protected $time;

    public static function fromDocument(DocumentInterface $doc)
    {
        $showcase = new static();
        $showcase->application = Application::fromDocumentMetadata($doc->getMetadata());
        $showcase->source = basename($doc->getPath());
        $showcase->time = $doc->getTime();
        return $showcase;
    }

    public static function fromJson(array $json, $directory)
    {
        $showcase = new static();
        if (!empty($json['source'])) {
            $showcase->source = $json['source'];
        }
        if (!empty($json['application'])) {
            $showcase->application = Application::fromJson($json['application']);
        }
        if (!empty($json['patterns'])) {
            foreach ($json['patterns'] as $pattern) {
                $showcase->patterns[$pattern['id']] = Pattern::fromJson($pattern, $directory);
            }
        }
        if (!empty($json['time'])) {
            $showcase->time = new \DateTime($json['time']);
        }
        return $showcase;
    }

    public static function load($path)
    {
        if (!is_file($path)) {
            throw new \InvalidArgumentException("Not a file: $path");
        }
        $json = static::jsonDecode(file_get_contents($path));
        if (null === $json) {
            throw new \DomainException("Empty or invalid file: $path");
        }
        $showcase = static::fromJson($json, dirname($path));
        return $showcase;
    }

    public static function save(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $filesCreated = [];
        $file = $input->getArgument('file');
        $path = $input->getArgument('path') ?: dirname($file);
        $doc = new Document($file);
        $formats = [
          ['1x,2x', 'png'],
        ];
        $prefix = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $showcase = Showcase::fromDocument($doc);
        foreach ($doc->getArtboards() as $artboard) {
            if (!$artboard->isPattern()) {
                continue;
            }
            $pattern = $artboard->getPattern();
            if (!$showcase->hasPattern($pattern)) {
                $showcase->addPattern(new Pattern($pattern));
            }
            $mockup = Mockup::fromArtboard($artboard);
            foreach ($formats as $f) {
                list($scale, $format) = $f;
                $images = $doc->export($artboard, $path, $format, $scale);
                $output->writeln(sprintf("Exported %d files from artboard \"%s\"", count($images), $artboard->getName()), Output::VERBOSITY_VERBOSE);
                foreach ($images as $image) {
                    $mockup->addImage($image);
                    $filesCreated[] = $prefix . $image->getPath();
                }
            }
            $showcase->getPattern($pattern)->addMockup($mockup);
        }

        $stop = microtime(true);
        $elapsed = $stop - $start;

        $reportPath = $prefix . 'showcase.json';
        $report = json_encode($showcase, JSON_PRETTY_PRINT);
        file_put_contents($reportPath, $report);
        $output->writeln('Report saved.', Output::VERBOSITY_VERBOSE);
        $filesCreated[] = $reportPath;

        $output->writeln(sprintf("%d files created.", count($filesCreated)));

        $output->writeln(sprintf("Done. (%0.04d seconds)", $elapsed));
    }

    protected static function jsonDecode($str)
    {
        return json_decode($str, true);
    }

    protected static function makeExportPath(ArtboardInterface $artboard, $scale, $format)
    {
        $pattern = $artboard->getPattern();
        $width = $artboard->getWidth();
        $height = $artboard->getHeight();
        return "{$pattern}__{$width}x{$height}__{$scale}.{$format}";
    }

    public function addPattern(PatternInterface $pattern)
    {
        $this->patterns[$pattern->getId()] = $pattern;
    }

    /**
     * @return ApplicationInterface
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param string $id
     * @return PatternInterface
     */
    public function getPattern($id)
    {
        if (!$this->hasPattern($id)) {
            throw new \OutOfBoundsException("Unknown pattern: $id");
        }
        return $this->patterns[$id];
    }

    /**
     * @return PatternInterface[]
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function hasPattern($id)
    {
        return isset($this->patterns[$id]);
    }

    public function jsonSerialize()
    {
        return [
          'patterns' => $this->patterns,
          'application' => $this->application,
          'source' => $this->source,
          'time' => $this->time->format('c'),
        ];
    }
}
