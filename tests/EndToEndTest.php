<?php

declare(strict_types=1);

namespace LeanBookTools\Test;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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
    public function testCompareOutputForSingleTestCases(SplFileInfo $testFile): void
    {
        $output = $this->runPhpUnitWithCleanOutputForTest($testFile->getPathname());
        $outputFile = $testFile->getPath() . '/' . $testFile->getFilenameWithoutExtension() . '.output.txt';
        $this->assertFileExists($outputFile, 'First create a file with the expected output');
        $this->assertSame(file_get_contents($outputFile), $output);
    }

    public static function namesProvider(): Iterator
    {
        foreach (Finder::create()->name('*Test.php')->in(self::PROJECT_DIR . '/tests/') as $testFile) {
            yield [$testFile];
        }
    }

    private function runPhpUnitWithCleanOutputForTest(string $testName): string
    {
        $process = new Process([
            'vendor/bin/phpunit',
            $testName,
        ], self::PROJECT_DIR);
        $process->run();

        return $process->getOutput();
    }
}
