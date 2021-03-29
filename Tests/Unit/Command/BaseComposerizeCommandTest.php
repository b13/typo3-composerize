<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Tests\Unit\Command;

use B13\Typo3Composerize\Command\BaseComposerizeCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class BaseComposerizeCommandTest extends TestCase
{
    /** @var CommandTester */
    private $commandTester;

    protected function setUp(): void
    {
        $application = new Application();
        $application->add(new BaseComposerizeCommand());
        $command = $application->find('base');
        $this->commandTester = new CommandTester($command);
    }

    public function testGetArguments()
    {
        $options = ['extension' => ['extension1', 'extension2'], '-d' => __DIR__ . '/../../Fixtures/', '-f' => ['folder1', 'folder2']];
        $this->commandTester->execute($options);
        $baseCommand = new BaseComposerizeCommand();
        $arguments = $baseCommand->getArguments($this->commandTester->getInput());

        // Test extension argument
        $this->assertIsArray($arguments[0]);
        $this->assertIsString($arguments[0][0]);
        $this->assertIsString($arguments[0][1]);

        // Test -d (doc-root) option
        $this->assertFileExists($arguments[1]);

        // Test -f (folders) option
        $this->assertIsArray($arguments[2]);
        $this->assertIsString($arguments[2][0]);
        $this->assertIsString($arguments[2][1]);
    }
}
