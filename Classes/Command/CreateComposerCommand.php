<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Command;

use B13\Typo3Composerize\Utilities\ComposerConvertUtility;
use Composer\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
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
        $this->addOption('folders', 'f', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Paths to scan for extensions relative to doc-root', ['typo3conf/ext/', 'typo3/sysext/']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extension = $input->getArgument('extension');
        $extensionArray = is_null($extension) ? [] : explode(',', $extension);
        $docRoot = $input->getOption('doc-root');
        $folders = $input->getOption('folders');

        $utility = new ComposerConvertUtility($docRoot, $folders);
        $extensions = $utility->validateExtensions($extensionArray);

        foreach ($extensions as $extension) {
            if (!$extension['extra-extension-key'] && !$extension['composer-json']) {
                $pathComposer = $utility->convertEmconfToComposer($extension['path']);

                // Validate generated composer.json
                putenv('COMPOSER=' . $pathComposer);
                $bufferedOutput = new BufferedOutput();
                $validate = new ArrayInput(['command' => 'validate']);
                $checkApplication = new Application();
                $checkApplication->setAutoExit(false);
                $state = $checkApplication->run($validate, $bufferedOutput);

                if ($state) {
                    $output->writeln('<fg=red>ERROR</> EXT:' . $extension['ext-key'] . ' Validation failed, make sure you have set all values in ext_emconf.php properly' . PHP_EOL . $bufferedOutput->fetch());
                    unlink($pathComposer);
                } else {
                    $output->writeln('<fg=green>OK</> EXT:' . $extension['ext-key'] . ' Converted ext_emconf.php to valid composer.json');
                }

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
