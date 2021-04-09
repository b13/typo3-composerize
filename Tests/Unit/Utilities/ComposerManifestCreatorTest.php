<?php
declare(strict_types=1);

namespace B13\Typo3Composerize\Tests\Unit\Command;


use B13\Typo3Composerize\Utilities\ComposerManifestCreator;
use PHPUnit\Framework\TestCase;

class ComposerManifestCreatorTest extends TestCase
{

    public function testConvertToPackageName(): void
    {
        self::assertSame(
            'typo3-local/my-extension',
            ComposerManifestCreator::getFallbackPackageNameFromExtensionKey('my_extension')
        );
    }

    public function testGetPackageName(): void
    {
        $subject = new ComposerManifestCreator();
        // Test to get composer package name from TER
        self::assertSame(
            'georgringer/news',
            $subject->getPackageName('news')
        );

        // Test to get composer package name from map
        self::assertSame(
            'typo3/cms-core',
            $subject->getPackageName('typo3')
        );

        // Test specific case for defined php version
        self::assertSame(
            'php',
            $subject->getPackageName('php')
        );

        // Test case if package name neither found on TER nor in mapping
        self::assertSame(
            'typo3-local/not-existing-extension',
            $subject->getPackageName('not_existing_extension')
        );
    }

    public function testConvertConstraint(): void
    {
        $subject = new ComposerManifestCreator();
        // Test extension not existing on packagist, force version *
        self::assertEquals(
            [
                0 => 'typo3-local/naw-securedl',
                1 => '*',
            ],
            $subject->convertConstraint('naw_securedl', '1')
        );

        // Test empty
        self::assertEquals(
            [
                0 => 'typo3/cms-core',
                1 => '*',
            ],
            $subject->convertConstraint('typo3', '')
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
}
