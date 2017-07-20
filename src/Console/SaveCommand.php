<?php

namespace Pixo\Showcase\Console;

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
        $doc = new Document($file);
        $formats = [
            ['1x,2x', 'png'],
        ];
        $prefix = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $showcase = Showcase::fromDocument($doc);
        foreach ($doc->getArtboards() as $artboard) {
            if (!$artboard->isPattern()) {
                continue;
            }
            $pattern = $artboard->getPattern();
            if (!$showcase->hasPattern($pattern)) {
                $showcase->addPattern(new Pattern($pattern));
            }
            $mockup = Mockup::fromArtboard($artboard);
            foreach ($formats as $f) {
                list($scale, $format) = $f;
                $images = $doc->export($artboard, $path, $format, $scale);
                $output->writeln(sprintf("Exported %d files from artboard \"%s\"", count($images), $artboard->getName()), Output::VERBOSITY_VERBOSE);
                foreach ($images as $image) {
                    $mockup->addImage($image);
                    $filesCreated[] = $prefix . $image->getPath();
                }
            }
            $showcase->getPattern($pattern)->addMockup($mockup);
        }

        $stop = microtime(true);
        $elapsed = $stop - $start;

        $manifestPath = Showcase::makeManifestPath($path);
        $manifest = json_encode($showcase, JSON_PRETTY_PRINT);
        file_put_contents($manifestPath, $manifest);
        $output->writeln('Manifest saved.', Output::VERBOSITY_VERBOSE);
        $filesCreated[] = $manifestPath;

        $output->writeln(sprintf("%d files created.", count($filesCreated)));

        $output->writeln(sprintf("Done. (%0.04d seconds)", $elapsed));
    }
}
