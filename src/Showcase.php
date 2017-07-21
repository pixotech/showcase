<?php

namespace Pixo\Showcase;

use Pixo\Showcase\Exceptions\InvalidManifestException;
use Pixo\Showcase\Exceptions\MissingManifestException;
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
            throw new MissingManifestException($manifestPath);
        }
        $json = static::jsonDecode(file_get_contents($manifestPath));
        if (null === $json) {
            throw new InvalidManifestException($manifestPath);
        }
        $showcase = static::fromJson($json, $path);
        return $showcase;
    }

    /**
     * @param string $dir
     * @return string
     */
    public static function makeManifestPath($dir)
    {
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::MANIFEST_NAME;
    }

    /**
     * @param string $str
     * @return mixed
     */
    protected static function jsonDecode($str)
    {
        return json_decode($str, true);
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
