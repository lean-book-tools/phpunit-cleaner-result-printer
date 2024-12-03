<?php

declare(strict_types=1);

namespace LeanBookTools;

use LeanBookTools\Printer\SimplePrinter;
use LeanBookTools\Subscribers\Test\TestErroredSubscriber;
use LeanBookTools\Subscribers\Test\TestFailedSubscriber;
use LeanBookTools\Subscribers\Test\TestFinishedSubscriber;
use LeanBookTools\Subscribers\Test\TestPassedSubscriber;
use LeanBookTools\Subscribers\Test\TestPreparedSubscriber;
use LeanBookTools\Subscribers\TestRunner\TestRunnerFinishedSubscriber;
use LeanBookTools\Subscribers\TestRunner\TestRunnerStartedSubscriber;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Logging\TestDox\TestResultCollector;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Output\DefaultPrinter;

/**
 * Registered in phpunit.xml
 */
final readonly class CleanerResultPrinterExtension implements Extension
{
    private SimplePrinter $simplePrinter;

    public function __construct()
    {
        $this->simplePrinter = new SimplePrinter(DefaultPrinter::standardOutput());
    }

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        if ($configuration->noOutput()) {
            return;
        }

        $testResultCollector = new TestResultCollector(new EventFacade(), new IssueFilter($configuration->source()));

        // very important to replace output with ours
        $facade->replaceOutput();

        $facade->registerSubscribers(
            // single test
            new TestPreparedSubscriber($this->simplePrinter, $testResultCollector),
            new TestFailedSubscriber($this->simplePrinter, $testResultCollector),
            new TestErroredSubscriber($this->simplePrinter, $testResultCollector),
            new TestFinishedSubscriber($this->simplePrinter, $testResultCollector),
            new TestPassedSubscriber($this->simplePrinter, $testResultCollector),

            // test runner
            new TestRunnerStartedSubscriber($this->simplePrinter, $testResultCollector),
            new TestRunnerFinishedSubscriber($this->simplePrinter, $testResultCollector),
        );
    }
}
