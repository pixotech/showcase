<?php

namespace Pixo\Showcase;

class Image implements ImageInterface, \JsonSerializable
{
    protected $path;

    protected $format;

    protected $scale = 1;

    public static function fromJson(array $json)
    {
        $image = new static();
        $image->path = $json['path'];
        $image->format = $json['format'];
        $image->scale = $json['scale'];
        return $image;
    }

    public static function fromPath($path)
    {
        $image = new static();
        $image->path = $path;
        if (preg_match('/(@[.0-9]+x)?\.([a-z]+)$/', $path, $matches)) {
            list(, $scale, $format) = $matches;
            $image->format = $format;
            if ($scale) {
                $image->scale = floatval(substr($scale, 1, -1));
            }
        }
        return $image;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function jsonSerialize()
    {
        return [
            'path' => $this->path,
            'format' => $this->format,
            'scale' => $this->scale,
        ];
    }
}
