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
        $expectedResult = <<<EOF
+------------------+-----------------+--------------------------------------+---------------------------------------+
| Extension Key    | composer.json ? | extension-key set in composer.json ? | Package name                          |
+------------------+-----------------+--------------------------------------+---------------------------------------+
| sample_extension | yes             | no                                   | b13/test-package                      |
| third_extension  | yes             | yes                                  | b13/third-extension                   |
| second_extension | no              | no                                   | Preview: typo3-local/second-extension |
+------------------+-----------------+--------------------------------------+---------------------------------------+
EOF;

        $this->assertEquals($expectedResult, trim($this->commandTester->getDisplay()));
    }
}
