<?php

namespace Pixo\Showcase\Sketch;

class Artboard implements \JsonSerializable, ArtboardInterface
{
    protected $height;

    protected $id;

    protected $name;

    protected $page;

    protected $pattern;

    protected $width;

    public static function fromDocumentJson(array $json, array $page)
    {
        $exp = new static();
        $exp->id = $json['id'];
        $exp->name = $json['name'];
        $exp->width = $json['rect']['width'];
        $exp->height = $json['rect']['height'];
        $exp->page = $page['name'];

        if (preg_match('/@([a-z0-9-]+)\b/', $json['name'], $matches)) {
            $exp->pattern = $matches[1];
        }

        return $exp;
    }

    public static function fromJson(array $json)
    {
        $exp = new static();
        $exp->id = $json['id'];
        $exp->name = $json['name'];
        $exp->page = $json['page'];
        $exp->pattern = $json['pattern'];
        $exp->width = $json['width'];
        $exp->height = $json['height'];
        return $exp;
    }

    public function __debugInfo()
    {
        return $this->jsonSerialize();
    }

    public function __toString()
    {
        return (string)$this->getName();
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

    public function getPage()
    {
        return $this->page;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function isPattern()
    {
        return !empty($this->pattern);
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'page' => $this->page,
            'pattern' => $this->pattern,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
