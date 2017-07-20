<?php

namespace Pixo\Showcase\Sketch;

class Application implements ApplicationInterface, \JsonSerializable
{
    protected $name;

    protected $version;

    public static function fromDocumentMetadata(array $metadata)
    {
        $app = new static();
        $app->name = $metadata['app'];
        $app->version = $metadata['appVersion'];
        return $app;
    }

    public static function fromJson(array $json)
    {
        $app = new static();
        $app->name = $json['name'];
        $app->version = $json['version'];
        return $app;
    }

    public function __debugInfo()
    {
        return $this->jsonSerialize();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
        ];
    }
}
