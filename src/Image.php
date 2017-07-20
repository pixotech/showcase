<?php

namespace Pixo\Showcase;

class Image implements ImageInterface, \JsonSerializable
{
    protected $directory;

    protected $format;

    protected $path;

    protected $scale = 1;

    protected $source;

    public static function fromJson(array $json, $directory)
    {
        $image = new static();
        $image->directory = $directory;
        $image->source = $json['source'];
        $image->path = $json['path'];
        $image->format = $json['format'];
        $image->scale = $json['scale'];
        return $image;
    }

    public static function fromPath($path, $directory)
    {
        $image = new static();
        $image->directory = $directory;
        $image->path = $path;
        if (preg_match('/([-A-Z0-9]+)(@[.0-9]+x)?\.([a-z]+)$/', $path, $matches)) {
            list(, $image->source, $scale, $image->format) = $matches;
            if ($scale) {
                $image->scale = floatval(substr($scale, 1, -1));
            }
        }
        return $image;
    }

    public function getFile()
    {
        return new \SplFileInfo($this->directory . DIRECTORY_SEPARATOR . $this->path);
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getScale()
    {
        return $this->scale;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function jsonSerialize()
    {
        return [
            'path' => $this->path,
            'source' => $this->source,
            'format' => $this->format,
            'scale' => $this->scale,
        ];
    }
}
