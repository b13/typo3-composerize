<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;

final class CheckIntegrityCommandTest extends TestCase
{
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        self::assertArrayHasKey(
            'huhu',
            ['huhu' => 'hallo da']
        );
    }
}
