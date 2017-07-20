<?php

namespace Pixo\Showcase\Console;

use Pixo\Showcase\Showcase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InspectCommand extends Command
{
    public function __construct($name = 'inspect')
    {
        parent::__construct($name);
        $this->setDescription("Display information about a directory of mockups");
        $this->addArgument('path', InputArgument::REQUIRED, 'The path to the directory');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $showcase = Showcase::load($path);
        ob_start();
        var_dump($showcase);
        $dump = ob_get_contents();
        ob_end_clean();
        $output->writeln($dump);
    }
}
