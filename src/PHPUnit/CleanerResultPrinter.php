<?php

declare(strict_types=1);

namespace LeanBookTools\PHPUnit;

use function count;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\DefaultResultPrinter;

final class CleanerResultPrinter extends DefaultResultPrinter
{
    public function __construct(
        $out = null,
        bool $verbose = false,
        string $colors = self::COLOR_DEFAULT,
        bool $debug = false
    ) {
        // Override the line length, to make the output fit the book pages

        parent::__construct($out, $verbose, $colors, $debug, 70, false);
    }

    public function cleanUpTestName(string $testName): string
    {
        $result = preg_match('/^(.+Test)::/', $testName, $matches);
        if ($result === 0) {
            return $testName;
        }

        $testClass = $matches[1];

        $classNameParts = explode('\\', $testClass);
        $simpleClassName = end($classNameParts);
        assert(is_string($simpleClassName));

        // Strip the namespace from the test class
        $testName = str_replace($testClass, $simpleClassName, $testName);

        // Remove dump of data set (e.g. SmartFileInfo)
        $testName = preg_replace('/(data sets #(\d+)).+/', '$1', $testName);

        return trim($testName, ' ');
    }

    public function cleanUpExceptionMessage(string $message): string
    {
        // remove directory from file paths
        $lines = explode("\n", $message);
        foreach ($lines as $key => $line) {
            $result = preg_match('/(.+\.(php|php\.inc))/', $line, $matches);
            if ($result === 0) {
                continue;
            }

            $filePath = $matches[0];
            $simplifiedFilePath = pathinfo($filePath, PATHINFO_BASENAME);
            $lines[$key] = str_replace($filePath, $simplifiedFilePath, $line);
        }

        return implode("\n", $lines);
    }

    protected function printHeader(TestResult $result): void
    {
        if (count($result) > 0) {
            // This prevents variation between test runs
            $this->write(PHP_EOL . PHP_EOL . "Time: 00:00.782, Memory: 64.50 MB\n");
        }
    }

    protected function printDefectHeader(TestFailure $defect, int $count): void
    {
        $this->write(sprintf("\n%d) %s\n", $count, $this->cleanUpTestName($defect->getTestName())));
    }

    protected function printDefectTrace(TestFailure $defect): void
    {
        $e = $defect->thrownException();

        $this->write($this->cleanUpExceptionMessage((string) $e));

        while ($e = $e->getPrevious()) {
            $this->write("\nCaused by\n" . $this->cleanUpExceptionMessage((string) $e));
        }
    }
}
