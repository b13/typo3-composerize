<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Tests\Unit\Command;

use B13\Typo3Composerize\Command\CheckIntegrityCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class CheckIntegrityCommandTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = new Application();
        $application->add(new CheckIntegrityCommand());
        $command = $application->find('check');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteCheckIntegrityCommand()
    {
        $this->commandTester->execute(['command' => 'check', '-d' => __DIR__ . '/../../Fixtures/', '-f' => ['.']]);

        self::assertStringContainsString('Preview: typo3-local/second-extension', $this->commandTester->getDisplay());
        self::assertStringContainsString('b13/test-package', $this->commandTester->getDisplay());
        self::assertStringContainsString('b13/third-extension', $this->commandTester->getDisplay());
    }
}
