<?php

namespace Pixo\Showcase\Console;

use Pixo\Showcase\Image;
use Pixo\Showcase\ImageInterface;
use Pixo\Showcase\Mockup;
use Pixo\Showcase\Pattern;
use Pixo\Showcase\Showcase;
use Pixo\Showcase\Sketch\Document;
use Pixo\Showcase\Sketch\DocumentInterface;
use Pixo\Showcase\Sketch\Exceptions\ExportException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
        $patternCount = $mockupCount = $imageCount = 0;
        if (count($artboards)) {
            $output->write('Exporting from Sketch...');
            $exports = $this->exportArtboards($doc, array_keys($artboards), $path);
            $output->writeln('<info>done.</info>');

            foreach ($exports as $id => $images) {
                $artboard = $artboards[$id];
                $pattern = $artboard->getPattern();
                if (!$showcase->hasPattern($pattern)) {
                    $showcase->addPattern(new Pattern($pattern));
                    $patternCount++;
                }
                $mockup = Mockup::fromArtboard($artboard);
                $mockupCount++;
                foreach ($images as $image) {
                    $mockup->addImage($image);
                    $imageCount++;
                }
                $showcase->getPattern($pattern)->addMockup($mockup);
            }
        }
        $output->writeln(sprintf('Created <comment>%d images</comment>', $imageCount));
        $output->writeln(sprintf('Found <comment>%d patterns</comment>', $patternCount));

        $stop = microtime(true);
        $elapsed = $stop - $start;

        $manifestPath = Showcase::makeManifestPath($path);
        $manifest = json_encode($showcase, JSON_PRETTY_PRINT);
        file_put_contents($manifestPath, $manifest);
        $output->writeln("Manifest saved to <info>$manifestPath</info>");
        $filesCreated[] = $manifestPath;

        $output->writeln(sprintf("<info>Save complete</info> (%0.04d seconds)", $elapsed));
    }

    /**
     * @param DocumentInterface $doc
     * @param array $ids An array of artboard IDs
     * @param string $path
     * @return array
     * @throws \Exception
     */
    public function exportArtboards(DocumentInterface $doc, array $ids, $path)
    {
        $cmd = sprintf('sketchtool export artboards %s', escapeshellarg($doc->getPath()));
        $cmd .= sprintf(' --items=%s', escapeshellarg(implode(',', $ids)));
        $cmd .= sprintf(' --output=%s', escapeshellarg($path));
        $cmd .= sprintf(' --formats=%s', escapeshellarg(self::DEFAULT_FORMATS));
        $cmd .= sprintf(' --scales=%s', escapeshellarg(self::DEFAULT_SCALES));
        $cmd .= ' --use-id-for-name=YES';
        $cmd .= ' --save-for-web=YES';

        $proc = new Process($cmd);
        $proc->run();
        if (!$proc->isSuccessful()) {
            throw new ExportException();
        }
        $exports = [];
        $lines = explode("\n", $proc->getOutput());
        foreach ($lines as $line) {
            if (preg_match('/^Exported (.+)$/', $line, $matches)) {
                $image = Image::fromPath(basename($matches[1]), $path);
                $exports[$image->getSource()][] = $image;
            }
        }
        return $exports;
    }

    protected function getExportedFilePath($dir, ImageInterface $image)
    {
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $image->getPath();
    }
}
