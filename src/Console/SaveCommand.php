<?php

namespace Pixo\Showcase\Console;

use Pixo\Showcase\Image;
use Pixo\Showcase\ImageInterface;
use Pixo\Showcase\Mockup;
use Pixo\Showcase\Pattern;
use Pixo\Showcase\Showcase;
use Pixo\Showcase\Sketch\Artboard;
use Pixo\Showcase\Sketch\ArtboardInterface;
use Pixo\Showcase\Exceptions\ExportException;
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

        $file = $input->getArgument('file');
        if (!is_file($file)) {
            $output->writeln("<error>Not a file: $file</error>");
            return;
        } else {
            $output->writeln("Source file is <info>$file</info>");
        }

        $path = $input->getArgument('path');
        if (!is_dir($path)) {
            $output->writeln("<error>Not a directory: $path</error>");
            return;
        } else {
            $output->writeln("Destination directory is <info>$path</info>");
        }

        $artboards = [];
        foreach ($this->getDocumentArtboards($file) as $artboard) {
            if (!$artboard->isPattern()) {
                continue;
            }
            $artboards[$artboard->getId()] = $artboard;
        }
        $output->writeln(sprintf("Found mockups in <comment>%s artboards</comment>", count($artboards)));

        $showcase = Showcase::fromDocument($file, $this->getDocumentMetadata($file));

        $patternCount = $mockupCount = $imageCount = 0;
        if (count($artboards)) {
            $output->write('Exporting from Sketch...');
            $exports = $this->exportArtboards($file, array_keys($artboards), $path);
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
     * @param string $file
     * @param array $ids An array of artboard IDs
     * @param string $path
     * @return array
     * @throws \Exception
     */
    public function exportArtboards($file, array $ids, $path)
    {
        $cmd = sprintf('sketchtool export artboards %s', escapeshellarg($file));
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

    /**
     * @param string $file
     * @return ArtboardInterface[]
     * @throws \Exception
     */
    public function getDocumentArtboards($file)
    {
        $artboards = [];
        $cmd = sprintf("sketchtool list artboards %s", escapeshellarg($file));
        $cmd .= " --include-symbols=YES";
        $proc = new Process($cmd);
        $proc->run();
        if (!$proc->isSuccessful()) {
            throw new \Exception("Could not retrieve artboards");
        }
        $data = json_decode($proc->getOutput(), true);
        foreach ($data['pages'] as $page) {
            foreach ($page['artboards'] as $artboard) {
                $artboards[$artboard['id']] = Artboard::fromDocumentJson($artboard, $page);
            }
        }
        return $artboards;
    }

    /**
     * @param string $file
     * @return array
     * @throws \Exception
     */
    public function getDocumentMetadata($file)
    {
        $cmd = sprintf("sketchtool metadata %s", escapeshellarg($file));
        $proc = new Process($cmd);
        $proc->run();
        if (!$proc->isSuccessful()) {
            throw new \Exception("Could not retrieve metadata");
        }
        return json_decode($proc->getOutput(), true);
    }

    protected function getExportedFilePath($dir, ImageInterface $image)
    {
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $image->getPath();
    }
}
