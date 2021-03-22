<?php

// src/Command/CreateUserCommand.php
namespace B13\Typo3Composerize\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckIntegrityCommand extends Command
{
    protected static $defaultName = 'check-integrity';

    protected function configure()
    {
        $this->setDescription('Check TYPO3 extensions for composer compatability.')
            ->setHelp('Check TYPO3 extensions for composer compatability.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Some awesome integrity checks ...');

        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;
    }
}
