<?php

namespace Pixo\Showcase;

abstract class Exportable implements \JsonSerializable, ExportableInterface
{
    protected $description;

    protected $height;

    protected $id;

    protected $name;

    protected $patternId;

    protected $width;

    public static function parseName($name)
    {
        $parsed = [
            'artboard' => $name,
            'pattern' => null,
            'name' => null,
            'group' => null,
            'extra' => null,
        ];
        if (preg_match('/@([a-z0-9-]+)\b/', $name, $matches)) {
            $pattern = $matches[1];
            list($name, $extra) = explode("@{$pattern}", $name, 2);
            $path = array_map('trim', explode('/', $name));
            $name = array_pop($path);
            $group = implode('/', $path);
            $parsed['pattern'] = $pattern;
            $parsed['name'] = $name ?: $pattern;
            $parsed['group'] = $group ?: null;
            $parsed['extra'] = trim($extra) ?: null;

        } else {
            $parsed['name'] = $name;
        }
        return $parsed;
    }

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->setName($data['name']);
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPatternId()
    {
        return $this->patternId;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function isPattern()
    {
        return !empty($this->patternId);
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'width' => $this->width,
            'height' => $this->height,
            'pattern' => $this->patternId,
        ];
    }

    protected function setName($name)
    {
        $this->name = $name;
        $parsed = self::parseName($name);
        $this->patternId = $parsed['pattern'];
   }
}
