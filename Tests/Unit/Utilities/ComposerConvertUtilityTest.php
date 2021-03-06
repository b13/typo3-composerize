<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Tests\Unit\Command;

use B13\Typo3Composerize\Utilities\ComposerConvertUtility;
use PHPUnit\Framework\TestCase;

final class ComposerConvertUtilityTest extends TestCase
{
    const UPDATED_JSON_FILE = 'composer_ext_key.json';
    const EMCONF_CONVERTED_JSON_FILE = 'emconf-converted.json';

    protected string $extensionPath;
    protected ComposerConvertUtility $utility;

    public function setUp(): void
    {
        $this->extensionPath = __DIR__ . '/../../Fixtures/sample_extension';
        $this->utility = new ComposerConvertUtility(__DIR__ . '/../../Fixtures/', ['.']);

        parent::setUp();
    }

    public function testValidateExtensions(): void
    {
        // Return only given by extension keys
        $extensionKeys = ['sample_extension', 'second_extension'];
        $extensions = $this->utility->validateExtensions($extensionKeys, ['./']);
        self::assertCount(2, $extensions);

        foreach ($extensions as $extension) {
            // Test if expected extensions are returned
            self::assertTrue(in_array($extension['ext-key'], $extensionKeys));

            // Test if update is really required
            self::assertTrue($extension['composer-json'] === false || $extension['extra-extension-key'] === false);
        }

        // Test valid extension - no update required
        $validExtensions = $this->utility->validateExtensions(['third_extension'], ['./']);
        self::assertCount(1, $validExtensions);
        foreach ($validExtensions as $extension) {
            self::assertTrue($extension['composer-json'] === true && $extension['extra-extension-key'] === 'third_extension');
        }
    }

    public function testGetExtensions(): void
    {
        self::assertCount(3, $this->utility->getExtensions());
    }

    public function testConvertEmconfToComposer(): void
    {
        $this->utility->convertEmconfToComposer($this->extensionPath, self::EMCONF_CONVERTED_JSON_FILE);
        $composerJson = json_decode(file_get_contents($this->extensionPath . '/' . self::EMCONF_CONVERTED_JSON_FILE), true);

        self::assertEquals([
            'typo3/cms-core' => '~8 || ~9 || ~10',
            'typo3/cms-beuser' => '*',
            'php' => '~7'
        ], $composerJson['require']);

        self::assertEquals('sample_extension', $composerJson['extra']['typo3/cms']['extension-key']);
        self::assertContains('Php/AnotherClass.php', $composerJson['autoload']['classmap']);
        self::assertContains('Classes/SampleClass.php', $composerJson['autoload']['classmap']);
    }

    public function testSetExtensionKey(): void
    {
        $this->utility->setExtensionKey($this->extensionPath, 'sample_extension', self::UPDATED_JSON_FILE);

        self::assertFileEquals($this->extensionPath . '/composer_expected.json', $this->extensionPath . '/' . self::UPDATED_JSON_FILE);
    }

    public function testLoadEmConf(): void
    {
        self::assertEquals($this->emConfSample(), $this->utility->loadEmConf('sample_extension', $this->extensionPath));
    }

    public function emConfSample(): array
    {
        return [
            'title' => 'Sample Extension',
            'description' => 'Extension for testing composer',
            'category' => 'fe',
            'author' => 'Sample Author',
            'author_email' => 'sample@author.com',
            'state' => 'stable',
            'clearCacheOnLoad' => false,
            'version' => '1.0.1',
            'constraints' => [
                'depends' => [
                    'typo3' => '8.5.0-10.4.99',
                    'beuser' => '',
                    'php' => '7.2.0-7.4.99'
                ],
                'conflicts' => [
                    'news' => '8.2.5',
                ],
                'suggests' => [
                    'workspaces' => '',
                ]
            ]
        ];
    }

    public function testGetExtensionClassMap(): void
    {
        $classMap = $this->utility->getExtensionClassMap($this->extensionPath);

        self::assertCount(2, $classMap);
        self::assertContains('Classes/SampleClass.php', $classMap);
        self::assertContains('Php/AnotherClass.php', $classMap);
    }

    public function tearDown(): void
    {
        if ($this->getName() === 'testSetExtensionKey') {
            @unlink($this->extensionPath . '/' . self::UPDATED_JSON_FILE);
        }

        if ($this->getName() === 'testConvertEmconfToComposer') {
            @unlink($this->extensionPath . '/' . self::EMCONF_CONVERTED_JSON_FILE);
        }

        parent::tearDown();
    }
}
