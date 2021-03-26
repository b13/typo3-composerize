<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Tests\Unit\Command;

use B13\Typo3Composerize\Utilities\ComposerConvertUtility;
use PHPUnit\Framework\TestCase;

final class ComposerConvertUtilityTest extends TestCase
{
    protected string $extensionPath;
    protected string $updatedJsonFile = 'composer_ext_key.json';
    protected ComposerConvertUtility $utility;

    public function setUp(): void
    {
        $this->extensionPath = __DIR__ . '/../../Fixtures/sample_extension';
        $this->utility = new ComposerConvertUtility('/some/path');

        parent::setUp();
    }

    public function testConvertToPackageName(): void
    {
        self::assertSame(
            'typo3-local/my-extension',
            ComposerConvertUtility::convertToPackageName('my_extension')
        );
    }

    public function testSetExtensionKey(): void
    {
        $utility = new ComposerConvertUtility('/some/path');
        $utility->setExtensionKey($this->extensionPath, 'sample_extension', $this->updatedJsonFile);

        self::assertFileExists($this->extensionPath . '/' . $this->updatedJsonFile, 'Generated file ' . $this->updatedJsonFile . ' does not exist!');
        self::assertFileEquals($this->extensionPath . '/composer_expected.json', $this->extensionPath . '/' . $this->updatedJsonFile);
    }

    public function testGetPackageName(): void
    {
        // Test to get composer package name from TER
        self::assertSame(
            'georgringer/news',
            $this->utility->getPackageName('news')
        );

        // Test to get composer package name from map
        self::assertSame(
            'typo3/cms-core',
            $this->utility->getPackageName('typo3')
        );

        // Test specific case for defined php version
        self::assertSame(
            'php',
            $this->utility->getPackageName('php')
        );

        // Test case if package name neither found on TER nor in mapping
        self::assertSame(
            'typo3-local/not-existing-extension',
            $this->utility->getPackageName('not_existing_extension')
        );
    }

    public function testConvertConstraint(): void
    {
        // Test extension not existing on packagist, force version *
        self::assertEquals(
            [
            0 => 'typo3-local/naw-securedl',
            1 => '*',
        ],
            $this->utility->convertConstraint('naw_securedl', '1')
        );

        // Test empty
        self::assertEquals(
            [
                0 => 'typo3/cms-core',
                1 => '*',
            ],
            $this->utility->convertConstraint('typo3', '')
        );

        // Test range between multiple versions
        self::assertEquals(
            [
                0 => 'typo3/cms-core',
                1 => '~7 || ~8 || ~9',
            ],
            $this->utility->convertConstraint('typo3', '7.0.0 - 9.4')
        );

        // Test single version
        self::assertEquals(
            [
                0 => 'typo3/cms-core',
                1 => '~7',
            ],
            $this->utility->convertConstraint('typo3', '7.0.0')
        );
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
                    "workspaces" => '',
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
            unlink($this->extensionPath . '/' . $this->updatedJsonFile);
        }

        parent::tearDown();
    }
}
