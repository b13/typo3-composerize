<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Command;

use B13\Typo3Composerize\Utilities\ComposerConvertUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckIntegrityCommand extends BaseComposerizeCommand
{
    protected static $defaultName = 'check';

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Check TYPO3 extensions for composer compatibility.')
            ->setHelp('Check TYPO3 extensions for composer compatibility.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($extensions, $docRoot, $folders) = $this->getArguments($input);

        $utility = new ComposerConvertUtility($docRoot, $folders);
        $extensions = $utility->validateExtensions($extensions);

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
