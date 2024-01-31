<?php

declare(strict_types=1);

namespace LeanBookTools\Test;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Process\Process;

final class EndToEndTest extends TestCase
{
    public const PROJECT_DIR = __DIR__ . '/../fixture/DemoProject';

    public static function setUpBeforeClass(): void
    {
        $projectDir = self::PROJECT_DIR;
        $process = new Process(['composer', 'update'], $projectDir);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new RuntimeException('Composer run not successful: ' . $process->getErrorOutput());
        }
    }

    #[DataProvider('namesProvider')]
    public function testCompareOutputForSingleTestCases(string $testName): void
    {
        $output = $this->runPhpUnitWithCleanOutputForTest($testName);
        $this->assertSame(file_get_contents(self::PROJECT_DIR . '/tests/' . $testName . '.output.txt'), $output);
    }

    public static function namesProvider(): Iterator
    {
        yield ['SuccessfulTest'];
        yield ['FailedTest'];
        yield ['ErrorTest'];
    }

    private function runPhpUnitWithCleanOutputForTest(string $testName): string
    {
        $process = new Process([
            'vendor/bin/phpunit',
            '--filter',
            $testName,
        ], self::PROJECT_DIR);
        $process->run();

        return $process->getOutput();
    }
}
