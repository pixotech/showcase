<?php

namespace Pixo\Showcase\Console;

use Pixo\Showcase\Exceptions\MissingManifestException;
use Pixo\Showcase\Showcase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ClearCommand extends Command
{
    public function __construct($name = 'clear')
    {
        parent::__construct($name);
        $this->setDescription("Clear a directory of exported files");
        $this->addArgument('path', InputArgument::REQUIRED, "The path of the exported files");
        $this->addOption('yes', 'y', null, "Answer 'yes' to all prompts");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $files = [];

        $manifestPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . Showcase::MANIFEST_NAME;
        if (is_file($manifestPath)) {
            $files[] = new \SplFileInfo($manifestPath);
        } else {
            $output->writeln("<error>Could not find a manifest at $manifestPath.</error>");
            return;
        }

        $showcase = Showcase::load($path);
        foreach ($showcase->getPatterns() as $pattern) {
            foreach ($pattern->getMockups() as $mockup) {
                foreach ($mockup->getImages() as $image) {
                    $file = $image->getFile();
                    if (!$file->isFile()) continue;
                    $files[] = $file;
                }
            }
        }

        if (!count($files)) {
            $output->writeln("<info>Could not find any files to delete.</info>");
            return;
        }

        if (!$input->getOption('yes')) {
            $output->writeln(sprintf('<comment>%d files will be deleted.</comment>', count($files)));
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Clear this directory?</question> [y/N] ', false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<info>Canceled.</info>');
                return;
            }
        }

        $deleted = [];
        $failed = [];
        foreach ($files as $file) {
            $output->write(sprintf("Deleting %s...", $file->getFilename()), false, Output::VERBOSITY_VERBOSE);
            if (unlink($file->getPathname())) {
                $output->writeln('<info>Done.</info>', Output::VERBOSITY_VERBOSE);
                $deleted[] = $file;
            } else {
                $output->writeln('<error>Failed.</error>', Output::VERBOSITY_VERBOSE);
                $failed[] = $file;
            }
        }

        $output->writeln(sprintf('<info>%d files were deleted.</info>', count($deleted)));
        if (count($failed)) {
            $output->writeln(sprintf('<comment>%d files could not be deleted.</comment>', count($failed)));
        }
    }
}
