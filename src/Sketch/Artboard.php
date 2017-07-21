<?php

namespace Pixo\Showcase\Sketch;

class Artboard implements \JsonSerializable, ArtboardInterface
{
    protected $description;

    protected $extra;

    protected $group;

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
            list($name, $extra) = explode("@{$exp->pattern}", $json['name'], 2);
            $path = array_map('trim', explode('/', $name));
            if (!empty($path)) {
                $exp->description = array_pop($path) ?: null;
            }
            if (!empty($path)) {
                $exp->group = implode('/', $path) ?: null;
            }
            if (!empty($extra)) {
                $exp->extra = trim($extra) ?: null;
            }
        }

        return $exp;
    }

    public static function fromJson(array $json)
    {
        $exp = new static();
        $exp->id = $json['id'];
        $exp->name = $json['name'];
        $exp->page = $json['page'];
        $exp->description = $json['description'];
        $exp->pattern = $json['pattern'];
        $exp->group = $json['group'];
        $exp->extra = $json['extra'];
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

    public function getDescription()
    {
        return $this->description;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function getGroup()
    {
        return $this->group;
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
            'description' => $this->description,
            'pattern' => $this->pattern,
            'group' => $this->group,
            'extra' => $this->extra,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
