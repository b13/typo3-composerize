<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Command;

use B13\Typo3Composerize\Utilities\ComposerConvertUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class CreateComposerCommand extends Command
{
    protected static $defaultName = 'create';

    protected function configure()
    {
        $this->setDescription('Create composer.json')
            ->setHelp('Creates a composer.json file for TYPO3 extensions based on ext_emconf.php and sets \'extra -> typo3/cms -> extension-key\'');
        $this->addArgument('extension', InputArgument::OPTIONAL, 'Path to the TYPO3 Project');
        $this->addOption('doc-root', 'd', InputOption::VALUE_REQUIRED, 'Path to the TYPO3 project document root', '.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extension = $input->getArgument('extension');
        $extensionArray = is_null($extension) ? [] : explode(',', $extension);
        $docRoot = $input->getOption('doc-root');

        $utility = new ComposerConvertUtility($docRoot);
        $extensions = $utility->validateExtensions($extensionArray);

        foreach ($extensions as $extension) {
            if (!$extension['extra-extension-key'] && !$extension['composer-json']) {
                $utility->convertEmconfToComposer($extension['path']);
                $output->writeln('<fg=green>OK</> EXT:' . $extension['ext-key'] . ' Convert ext_emconf.php to composer.json');
                continue;
            }

            if (!$extension['extra-extension-key']) {
                try {
                    $utility->setExtensionKey($extension['path'], $extension['ext-key']);
                    $output->writeln('<fg=green>OK</> EXT:' . $extension['ext-key'] . ' Add extension-key to existing composer.json');
                } catch (IOException $e) {
                    $output->writeln('<fg=red>ERROR</> EXT:' . $extension['ext-key'] . ' Failed to update composer.json `extension-key`');
                }
                continue;
            }

            $output->writeln('<fg=green>OK</> EXT:' . $extension['ext-key'] . ' - No update required');
        }

        return Command::SUCCESS;
    }
}
