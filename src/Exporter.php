<?php

namespace Pixo\Design\SketchPatterns;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class Exporter
{
    public function __invoke(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $filesCreated = [];
        $file = $input->getArgument('file');
        $path = $input->getArgument('path') ?: dirname($file);
        $doc = new Document($file);
        $formats = [
            ['1x', 'png'],
            ['2x', 'png'],
        ];
        $prefix = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $filemeta = $doc->getMetadata();

        $manifest = [
            'file' => basename($file),
            'time' => date('c'),
            'patterns' => [],
            'sketch' => [
                'app' => $filemeta['app'],
                'version' => $filemeta['appVersion'],
                'build' => $filemeta['build'],
            ],
        ];

        $patterns = $doc->getPatterns();
        $output->writeln(sprintf("<info>Found %d patterns</info>", count($patterns)), Output::VERBOSITY_VERBOSE);

        foreach ($doc->getArtboards() as $artboard) {
            $meta = Exportable::parseName($artboard->getName());
            if (!isset($meta['pattern'])) continue;

            $pattern = $meta['pattern'];
            if (!isset($manifest['patterns'][$pattern])) {
                $manifest['patterns'][$pattern] = [
                    'id' => $pattern,
                    'mockups' => [],
                ];
            }

            $mockup = [
                'artboard' => $artboard->getName(),
                'page' => $artboard->getPage(),
                'name' => $meta['name'],
                'group' => $meta['group'],
                'extra' => $meta['extra'],
                'width' => $artboard->getWidth(),
                'height' => $artboard->getHeight(),
                'images' => [],
            ];

            foreach ($formats as $f) {
                list($scale, $format) = $f;
                $exports = $doc->export($artboard, $path, $format, $scale);
                $output->writeln(sprintf("Exported %d files from artboard \"%s\"", count($exports), $artboard->getName()), Output::VERBOSITY_VERBOSE);
                $dest = $this->makeExportPath($artboard, $scale, $format);
                foreach ($exports as $i => $export) {
                    if (!$i) {
                        $output->writeln("Creating file: {$dest}", Output::VERBOSITY_VERBOSE);
                        $filesCreated[] = $prefix . $dest;
                        rename($export['path'], $prefix . $dest);
                        $mockup['images'][] = [
                            'path' => $dest,
                            'format' => $format,
                            'scale' => floatval($scale),
                        ];
                    } else {
                        $output->writeln("Deleting file: {$export['path']}", Output::VERBOSITY_VERBOSE);
                        unlink($export['path']);
                    }
                }
            }

            $manifest['patterns'][$pattern]['mockups'][] = $mockup;
        }

        $stop = microtime(true);
        $elapsed = $stop - $start;

        $reportPath = $prefix . 'patterns.json';
        $report = json_encode($manifest, JSON_PRETTY_PRINT);
        file_put_contents($reportPath, $report);
        $output->writeln('Report saved.', Output::VERBOSITY_VERBOSE);
        $filesCreated[] = $reportPath;

        $output->writeln(sprintf("%d files created.", count($filesCreated)));

        $output->writeln(sprintf("Done. (%0.04d seconds)", $elapsed));
    }

    protected function makeExportPath(ExportableInterface $artboard, $scale, $format)
    {
        $pattern = $artboard->getPatternId();
        $width = $artboard->getWidth();
        $height = $artboard->getHeight();
        return "{$pattern}__{$width}x{$height}__{$scale}.{$format}";
    }
}
