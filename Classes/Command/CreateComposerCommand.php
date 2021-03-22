<?php

// src/Command/CreateUserCommand.php
namespace B13\Typo3Composerize\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateComposerCommand extends Command
{
    protected static $defaultName = 'create';

    protected function configure()
    {
        $this->setDescription('Create composer.json')
            ->setHelp('Creates a composer.json file for TYPO3 extensions based on ext_emconf.php.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Let the magic happen...');

        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;
    }
}
