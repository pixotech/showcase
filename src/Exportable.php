<?php

namespace Pixo\Design\SketchPatterns;

abstract class Exportable implements \JsonSerializable, ExportableInterface
{
    protected $height;

    protected $id;

    protected $name;

    protected $patternId;

    protected $width;

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
        if (preg_match('/@([a-z0-9-]+)\b/', $name, $matches)) {
            $this->patternId = $matches[1];
        } else {
            $this->patternId = null;
        }
   }

}
