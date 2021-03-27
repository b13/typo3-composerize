<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Tests\Unit\Command;

use B13\Typo3Composerize\Command\CreateComposerCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateComposerCommandTest extends TestCase
{
    /** @var CommandTester */
    private $commandTester;

    protected function setUp(): void
    {
        $application = new Application();
        $application->add(new CreateComposerCommand());
        $command = $application->find('create');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute()
    {
        $this->commandTester->execute(['command' => 'create', '-d' => __DIR__ . '/../../Fixtures/', '-f' => ['.']]);
        $expectedResult = <<<EOF
OK EXT:sample_extension Add extension-key to existing composer.json
OK EXT:third_extension - No update required
OK EXT:second_extension Converted ext_emconf.php to valid composer.json
EOF;

        $this->assertEquals($expectedResult, trim($this->commandTester->getDisplay()));
    }

    protected function tearDown(): void
    {
        // Delete created composer.json
        @unlink(__DIR__ . '/../../Fixtures/second_extension/composer.json');

        // Remove extension-key from composer.json
        $json = file_get_contents(__DIR__ . '/../../Fixtures/sample_extension/composer.json');
        $jsonArray = json_decode($json, true);
        unset($jsonArray['extra']['typo3/cms']['extension-key']);
        file_put_contents(__DIR__ . '/../../Fixtures/sample_extension/composer.json', json_encode($jsonArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
