<?php

declare(strict_types=1);

namespace LeanBookTools;

use LeanBookTools\Naming\ClassNaming;

/**
 * @see \LeanBookTools\Test\OutputCleanerTest
 */
final class OutputCleaner
{
    // Override the line length, to make the output fit the book pages
    // parent::__construct($out, $verbose, $colors, $debug, 70, false);

    //    /**
    //     * @todo use somewhere
    //     */
    //    public static function cleanUpTestName(string $testName): string
    //    {
    //        $result = preg_match('/^(.+Test)::/', $testName, $matches);
    //        if ($result === 0) {
    //            return $testName;
    //        }
    //
    //        $testClass = $matches[1];
    //
    //        $simpleClassName = ClassNaming::resolveShortClassName($testClass);
    //
    //        // Strip the namespace from the test class
    //        $testName = str_replace($testClass, $simpleClassName, $testName);
    //
    //        // Remove dump of data set (e.g. SmartFileInfo)
    //        /** @var string */
    //        $testName = preg_replace('/(data set #(\d+)).+/', '$1', $testName);
    //
    //        return trim($testName, ' ');
    //    }

    public static function cleanUpExceptionMessage(string $message): string
    {
        // remove directory from file paths
        $lines = explode("\n", $message);
        foreach ($lines as $key => $line) {
            if (str_contains($line, 'vendor/phpunit')) {
                unset($lines[$key]);
                continue;
            }

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
}
