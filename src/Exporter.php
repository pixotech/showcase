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

        $patterns = $doc->getPatterns();
        $output->writeln(sprintf("<info>Found %d patterns</info>", count($patterns)), Output::VERBOSITY_VERBOSE);

        foreach ($doc->getPatterns() as $pattern => $artboards) {
            foreach ($artboards as $artboard) {
                foreach ($formats as $f) {
                    list($scale, $format) = $f;
                    $exports = $doc->export($artboard, $path, $format, $scale);
                    $output->writeln(sprintf("Exported %d files", count($exports)), Output::VERBOSITY_VERBOSE);
                    $dest = $prefix . $this->makeExportPath($artboard, $scale, $format);
                    foreach ($exports as $i => $export) {
                        if (!$i) {
                            $output->writeln("Creating file: {$dest}", Output::VERBOSITY_VERBOSE);
                            $filesCreated[] = $dest;
                            rename($export['path'], $dest);
                        } else {
                            $output->writeln("Deleting file: {$export['path']}", Output::VERBOSITY_VERBOSE);
                            unlink($export['path']);
                        }
                    }
                }
            }
        }

        $stop = microtime(true);
        $elapsed = $stop - $start;

        $output->writeln(sprintf("%d files created in %0.04d seconds.", count($filesCreated), $elapsed));
    }

    protected function makeExportPath(ExportableInterface $artboard, $scale, $format)
    {
        $pattern = $artboard->getPatternId();
        $width = $artboard->getWidth();
        $height = $artboard->getHeight();
        return "{$pattern}__{$width}x{$height}__{$scale}.{$format}";
    }
}
