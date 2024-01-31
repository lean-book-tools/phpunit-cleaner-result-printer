<?php

declare(strict_types=1);

namespace LeanBookTools\Test;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class EndToEndTest extends TestCase
{
    public const PROJECT_DIR = __DIR__ . '/../fixture/DemoProject';

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
    }

    private function runPhpUnitWithCleanOutputForTest(string $testName): string
    {
        $projectDir = self::PROJECT_DIR;
        $process = new Process(['composer', 'install'], $projectDir);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new \RuntimeException('Composer run not successful: ' . $process->getErrorOutput());
        }

        $process = new Process([
            'vendor/bin/phpunit',
            '--filter',
            $testName,
        ], $projectDir);
        $process->run();

        return $process->getOutput();
    }
}
