<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Command;

use B13\Typo3Composerize\Utilities\ComposerConvertUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckIntegrityCommand extends Command
{
    protected static $defaultName = 'check';

    protected function configure()
    {
        $this->setDescription('Check TYPO3 extensions for composer compatability.')
            ->setHelp('Check TYPO3 extensions for composer compatability.');
        $this->addArgument('extension', InputArgument::OPTIONAL, 'Path to the TYPO3 Project');
        $this->addOption('doc-root', 'd', InputOption::VALUE_REQUIRED, 'Path to the TYPO3 project document root', '.');
        $this->addOption('folders', 'f', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Paths to scan for extensions relative to doc-root', ['typo3conf/ext/', 'typo3/sysext/']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $extension = $input->getArgument('extension');
        $extensionArray = is_null($extension) ? [] : explode(',', $extension);
        $docRoot = $input->getOption('doc-root');
        $folders = $input->getOption('folders');

        $utility = new ComposerConvertUtility($docRoot, $folders);
        $extensions = $utility->validateExtensions($extensionArray);

        $tableRows = [];
        foreach ($extensions as $extension) {
            $tableRows[] = [
                'ext-key' => $extension['ext-key'],
                'composer-json' => $extension['composer-json'] ? '<fg=green>yes</>' : '<fg=red>no</>',
                'extra-extension-key' => $extension['extra-extension-key'] ? '<fg=green>yes</>' : '<fg=red>no</>',
                'package-name' => $extension['package-name'] ? $extension['package-name'] : '<fg=red>Preview: ' . ComposerConvertUtility::convertToPackageName($extension['ext-key']) . '</>',
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Extension Key', 'composer.json ?', 'extension-key set in composer.json ?', 'Package name'])->setRows($tableRows);
        $table->render();

        return Command::SUCCESS;
    }
}
