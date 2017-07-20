<?php

namespace Pixo\Showcase;

use Pixo\Showcase\Sketch\Application;
use Pixo\Showcase\Sketch\ApplicationInterface;
use Pixo\Showcase\Sketch\Document;
use Pixo\Showcase\Sketch\DocumentInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class Showcase implements ShowcaseInterface, \JsonSerializable
{
    const MANIFEST_NAME = 'showcase.manifest.json';

    const VERSION = '0.1.0';

    /**
     * @var ApplicationInterface
     */
    protected $application;

    /**
     * @var PatternInterface[]
     */
    protected $patterns = [];

    /**
     * @var string
     */
    protected $source;

    /**
     * @var \DateTime
     */
    protected $time;

    public static function inspect(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $showcase = static::load($path);
        ob_start();
        var_dump($showcase);
        $dump = ob_get_contents();
        ob_end_clean();
        $output->writeln($dump);
    }

    /**
     * @param \Pixo\Showcase\Sketch\DocumentInterface $doc
     * @return Showcase
     */
    public static function fromDocument(DocumentInterface $doc)
    {
        $showcase = new static();
        $showcase->application = Application::fromDocumentMetadata($doc->getMetadata());
        $showcase->source = basename($doc->getPath());
        $showcase->time = $doc->getTime();
        return $showcase;
    }

    /**
     * @param array $json
     * @param string $directory
     * @return Showcase
     */
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

    /**
     * @param string $path
     * @return Showcase
     */
    public static function load($path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException("Not a directory: $path");
        }
        $manifestPath = static::makeManifestPath($path);
        if (!is_file($manifestPath)) {
            throw new \InvalidArgumentException("Could not find manifest: $manifestPath");
        }
        $json = static::jsonDecode(file_get_contents($manifestPath));
        if (null === $json) {
            throw new \DomainException("Empty or invalid file: $manifestPath");
        }
        $showcase = static::fromJson($json, $path);
        return $showcase;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public static function save(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $filesCreated = [];
        $file = $input->getArgument('file');
        $path = $input->getArgument('path');
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

        $manifestPath = self::makeManifestPath($path);
        $manifest = json_encode($showcase, JSON_PRETTY_PRINT);
        file_put_contents($manifestPath, $manifest);
        $output->writeln('Manifest saved.', Output::VERBOSITY_VERBOSE);
        $filesCreated[] = $manifestPath;

        $output->writeln(sprintf("%d files created.", count($filesCreated)));

        $output->writeln(sprintf("Done. (%0.04d seconds)", $elapsed));
    }

    public static function version(InputInterface $input, OutputInterface $output)
    {
        return $output->writeln(self::VERSION);
    }

    /**
     * @param string $str
     * @return mixed
     */
    protected static function jsonDecode($str)
    {
        return json_decode($str, true);
    }

    /**
     * @param string $dir
     * @return string
     */
    protected static function makeManifestPath($dir)
    {
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::MANIFEST_NAME;
    }

    public function __construct()
    {
        $this->time = new \DateTime();
    }

    public function __debugInfo()
    {
        return $this->jsonSerialize();
    }

    /**
     * @param \Pixo\Showcase\PatternInterface $pattern
     */
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

    /**
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasPattern($id)
    {
        return isset($this->patterns[$id]);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'showcase' => [
                'version' => self::VERSION,
            ],
            'patterns' => $this->patterns,
            'application' => $this->application,
            'source' => $this->source,
            'time' => $this->time->format('c'),
        ];
    }
}
