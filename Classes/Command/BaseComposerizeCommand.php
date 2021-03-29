<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BaseComposerizeCommand extends Command
{
    protected static $defaultName = 'base';

    protected function configure()
    {
        $this->addArgument('extension', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Path to the TYPO3 Project', []);
        $this->addOption('doc-root', 'd', InputOption::VALUE_REQUIRED, 'Path to the TYPO3 project document root', '.');
        $this->addOption('folders', 'f', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Paths to scan for extensions relative to doc-root', ['typo3conf/ext/', 'typo3/sysext/']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }

    public function getArguments($input): array
    {
        $extensions = $input->getArgument('extension');
        $docRoot = $input->getOption('doc-root');
        $folders = $input->getOption('folders');

        return [$extensions, $docRoot, $folders];
    }
}
