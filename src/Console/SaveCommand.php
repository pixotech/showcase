<?php

namespace Pixo\Showcase\Console;

use Pixo\Showcase\ImageInterface;
use Pixo\Showcase\Mockup;
use Pixo\Showcase\Pattern;
use Pixo\Showcase\Showcase;
use Pixo\Showcase\Sketch\Document;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class SaveCommand extends Command
{
    const DEFAULT_FORMATS = 'png';

    const DEFAULT_SCALES = '1x,2x';

    public function __construct($name = 'save')
    {
        parent::__construct($name);
        $this->setDescription("Export pattern mockups to a directory");
        $this->addArgument('file', InputArgument::REQUIRED, 'The path to the Sketch file');
        $this->addArgument('path', InputArgument::REQUIRED, 'The path to the target directory');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $filesCreated = [];
        $file = $input->getArgument('file');
        $path = $input->getArgument('path');

        $output->writeln("Source file is <info>$file</info>");
        $output->writeln("Destination directory is <info>$path</info>");

        $doc = new Document($file);

        $artboards = [];
        foreach ($doc->getArtboards() as $artboard) {
            if (!$artboard->isPattern()) {
                continue;
            }
            $artboards[$artboard->getId()] = $artboard;
        }
        $output->writeln(sprintf("Found mockups in <comment>%s artboards</comment>", count($artboards)));

        $showcase = Showcase::fromDocument($doc);
        if (count($artboards)) {
            $output->write('Exporting from Sketch...');
            $images = $doc->export(array_keys($artboards), $path, self::DEFAULT_FORMATS, self::DEFAULT_SCALES);
            $output->writeln('<info>done.</info>');
            $output->writeln(sprintf("Exported <comment>%d files</comment>", count($images)));

            $mockups = [];
            foreach ($images as $image) {
                $mockups[$image->getSource()][] = $image;
            }

            foreach ($mockups as $id => $mockup) {
                $artboard = $artboards[$id];
                $pattern = $artboard->getPattern();
                if (!$showcase->hasPattern($pattern)) {
                    $showcase->addPattern(new Pattern($pattern));
                }
                $m = Mockup::fromArtboard($artboard);
                foreach ($mockup as $image) {
                    $m->addImage($image);
                }
                $showcase->getPattern($pattern)->addMockup($m);
            }
        }
        $output->writeln(sprintf('Found <comment>%d patterns</comment> in artboards', count($showcase->getPatterns())));

        $stop = microtime(true);
        $elapsed = $stop - $start;

        $manifestPath = Showcase::makeManifestPath($path);
        $manifest = json_encode($showcase, JSON_PRETTY_PRINT);
        file_put_contents($manifestPath, $manifest);
        $output->writeln("Manifest saved to <info>$manifestPath</info>");
        $filesCreated[] = $manifestPath;

        $output->writeln(sprintf("<info>Save complete</info> (%0.04d seconds)", $elapsed));
    }

    protected function getExportedFilePath($dir, ImageInterface $image)
    {
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $image->getPath();
    }
}
