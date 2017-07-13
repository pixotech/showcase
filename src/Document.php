<?php

namespace Pixo\Showcase;

class Document implements DocumentInterface
{
    protected $path;

    public function __construct($path)
    {
        if (!is_file($path)) {
            throw new \InvalidArgumentException("Not a file: $path");
        }
        $this->path = realpath($path);
    }

    public function export(ExportableInterface $exportable, $path, $format = null, $scale = null)
    {
        $cmd = sprintf('sketchtool export artboards %s', escapeshellarg($this->path));
        $cmd .= sprintf(' --item=%s', escapeshellarg($exportable->getId()));
        $cmd .= sprintf(' --output=%s', escapeshellarg($path));
        $cmd .= ' --use-id-for-name=YES';
        $cmd .= ' --save-for-web=YES';
        if (isset($format)) {
            $cmd .= sprintf(' --formats=%s', escapeshellarg($format));
        }
        if (isset($scale)) {
            $cmd .= sprintf(' --scales=%s', escapeshellarg($scale));
        }
        exec($cmd, $lines, $return);
        if ($return > 0) {
            throw new \Exception("Export failed");
        }
        $export = new Export($exportable, $path, $lines);
        return $export;
    }

    public function getArtboards($includeSymbols = true)
    {
        $artboards = [];
        $cmd = sprintf("sketchtool list artboards %s", escapeshellarg($this->path));
        if ($includeSymbols) {
            $cmd .= " --include-symbols=YES";
        }
        exec($cmd, $lines, $return);
        if ($return > 0) {
            throw new \Exception("Could not retrieve artboards");
        }
        $data = json_decode(implode("\n", $lines), true);
        foreach ($data['pages'] as $page) {
            foreach ($page['artboards'] as $artboard) {
                $artboards[$artboard['id']] = new Artboard($artboard, $page);
            }
        }
        return $artboards;
    }

    public function getMetadata()
    {
        $artboards = [];
        $cmd = sprintf("sketchtool metadata %s", escapeshellarg($this->path));
        exec($cmd, $lines, $return);
        if ($return > 0) {
            throw new \Exception("Could not retrieve metadata");
        }
        return json_decode(implode("\n", $lines), true);
    }

    public function getPatterns()
    {
        $patterns = [];
        foreach ($this->getArtboards() as $artboard) {
            if (!$artboard->isPattern()) continue;
            $pattern = $artboard->getPatternId();
            $id = $artboard->getId();
            if (!isset($patterns[$pattern][$id])) {
                $patterns[$pattern][$id] = $artboard;
            }
        }
        return $patterns;
    }

    public function getPath()
    {
        return $this->path;
    }
}
