<?php

namespace Pixo\Showcase\Console;

use Pixo\Showcase\Showcase;

class Application extends \Symfony\Component\Console\Application
{
    public function __construct()
    {
        parent::__construct('showcase', Showcase::VERSION);
        $this->add(new ClearCommand());
        $this->add(new InspectCommand());
        $this->add(new SaveCommand());
    }
}
