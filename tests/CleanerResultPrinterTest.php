<?php

declare(strict_types=1);

namespace LeanBookTools\Test;

use LeanBookTools\PHPUnit\CleanerResultPrinter;
use PHPUnit\Framework\TestCase;

final class CleanerResultPrinterTest extends TestCase
{
    private CleanerResultPrinter $cleanerResultPrinter;

    protected function setUp(): void
    {
        $this->cleanerResultPrinter = new CleanerResultPrinter();
    }

    public function testItAbbreviatesFilePathsInTheDefectTrace(): void
    {
        $expected = <<<'CODE_SAMPLE'
behavior-is-preserved.php.inc
Failed asserting that two strings are equal.
--- Expected
+++ Actual

AbstractRectorTestCase.php:111
AbstractRectorTestCase.php:95
MigrateToDateTimeImmutableRectorTest.php:18
CODE_SAMPLE;
        $trace = <<<'CODE_SAMPLE'
utils/rector/tests/Rector/MigrateToDateTimeImmutableRector/Fixture/behavior-is-preserved.php.inc
Failed asserting that two strings are equal.
--- Expected
+++ Actual

/app/vendor/rector/rector/packages/Testing/PHPUnit/AbstractRectorTestCase.php:111
/app/vendor/rector/rector/packages/Testing/PHPUnit/AbstractRectorTestCase.php:95
/app/manuscript-src/resources/src/MoreTestingTechniques/Ruleset/Version5/utils/rector/tests/Rector/MigrateToDateTimeImmutableRector/MigrateToDateTimeImmutableRectorTest.php:18
CODE_SAMPLE;

        self::assertEquals($expected, $this->cleanerResultPrinter->cleanUpExceptionMessage($trace));
    }

    public function testItCleansUpTheTestName(): void
    {
        self::assertEquals(
            'MigrateToDateTimeImmutableRectorTest::test with data set #1',
            $this->cleanerResultPrinter->cleanUpTestName(
                'MoreTestingTechniques\Ruleset\Version5\Utils\Rector\Tests\Rector\MigrateToDateTimeImmutableRector\MigrateToDateTimeImmutableRectorTest::test with data set #1 (RectorPrefix20210613\Symplify\SmartFileSystem\SmartFileInfo Object (...))'
            )
        );
    }
}
