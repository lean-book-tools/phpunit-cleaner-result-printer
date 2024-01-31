<?php

declare(strict_types=1);

namespace LeanBookTools\Subscribers\TestRunner;

use LeanBookTools\Naming\ClassNaming;
use LeanBookTools\OutputCleaner;
use LeanBookTools\Subscribers\AbstractSubscriber;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;
use PHPUnit\TestRunner\TestResult\Facade;
use PHPUnit\TextUI\Output\DefaultPrinter;
use PHPUnit\TextUI\Output\SummaryPrinter;
use ReflectionObject;

final class TestRunnerFinishedSubscriber extends AbstractSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        $testResult = Facade::result();

        // simple progress report
        if ($testResult->numberOfTestsRun() !== 0) {
            $reflectionProperty = (new ReflectionObject($testResult))->getProperty('numberOfTests');
            $totalNumberOfTests = $reflectionProperty->getValue($testResult);

            $this->simplePrinter->writeln(sprintf(
                '     %d / %d (%.0f%%)',
                $testResult->numberOfTestsRun(),
                $totalNumberOfTests,
                100 * ($testResult->numberOfTestsRun() / $totalNumberOfTests)
            ));
        }

        if ($testResult->numberOfTestsRun() !== 0) {
            $this->simplePrinter->newLine();
        }

        // print failed tests
        if ($testResult->hasTestFailedEvents()) {
            $this->printListHeaderWithNumber($testResult->numberOfTestFailedEvents(), 'failure');
            $this->printTestFailedEvents($testResult->testFailedEvents());
        }

        if ($testResult->hasTestErroredEvents()) {
            $this->printListHeaderWithNumber($testResult->numberOfTestErroredEvents(), 'error');
            $this->printTestErroredEvents($testResult->testErroredEvents());
        }

        if ($testResult->hasTestConsideredRiskyEvents()) {
            $this->printListHeaderWithNumber($testResult->numberOfTestsWithTestConsideredRiskyEvents(), 'risky test');
            $this->printTestConsideredRiskyEvents($testResult->testConsideredRiskyEvents());
        }

        $summaryPrinter = new SummaryPrinter(DefaultPrinter::standardOutput(), false);
        $summaryPrinter->print($testResult);
    }

    /**
     * Mimics @see \PHPUnit\TextUI\Output\Default\ResultPrinter::printListHeaderWithNumber()
     */
    private function printListHeaderWithNumber(int $number, string $type): void
    {
        $message = sprintf(
            "There %s %d %s%s:\n",
            ($number === 1) ? 'was' : 'were',
            $number,
            $type,
            ($number === 1) ? '' : 's',
        );

        $this->simplePrinter->writeln($message);
    }

    /**
     * @param Failed[] $testFailedEvents
     */
    private function printTestFailedEvents(array $testFailedEvents): void
    {
        $i = 1;

        foreach ($testFailedEvents as $testFailedEvent) {
            $title = $this->createTitle($testFailedEvent->test());
            $body = $testFailedEvent->throwable()->asString();

            $this->printListElement($i, $title, $body);
            $i++;
        }
    }

    /**
     * @param list<BeforeFirstTestMethodErrored|Errored> $events
     */
    private function printTestErroredEvents(array $events): void
    {
        $i = 1;

        foreach ($events as $event) {
            $title = $event instanceof Errored ? $this->createTitle($event->test()) : $event->testClassName();
            $body = $event->throwable()->asString();

            $this->printListElement($i, $title, $body);
            $i++;
        }
    }

    /**
     * @param array<string,list<ConsideredRisky>> $events
     */
    private function printTestConsideredRiskyEvents(array $events): void
    {
        $i = 1;

        foreach ($events as $elements) {
            foreach ($elements as $element) {
                $title = $this->createTitle($element->test());
                $body = $element->message();

                $this->printListElement($i, $title, $body);
                $i++;
            }
        }
    }

    /**
     * Mimics
     * @see \PHPUnit\TextUI\Output\Default\ResultPrinter::printListElement()
     */
    private function printListElement(int $number, string $title, string $body): void
    {
        $body = trim($body);
        $cleanBody = OutputCleaner::cleanUpExceptionMessage($body);

        $this->simplePrinter->writeln(
            sprintf(
                "%d) %s\n%s%s",
                $number,
                $title,
                $cleanBody,
                ! empty($cleanBody) ? "\n" : '',
            ),
        );
    }

    /**
     * Mimics
     * @see \PHPUnit\TextUI\Output\Default\ResultPrinter::name
     *
     * The result should be short class name and fixture number e.g. "MigrateToDateTimeImmutableRectorTest::test with data set #1"
     */
    private function createTitle(Test $test): string
    {
        if (! $test instanceof TestMethod) {
            return $test->name();
        }

        $shortClassName = ClassNaming::resolveShortClassName($test->className());

        $title = $shortClassName . '::' . $test->methodName();

        if ($test->testData()->hasDataFromDataProvider()) {
            $dataFromDataProvider = $test->testData()->dataFromDataProvider();
            $title .= ' with data set #' . $dataFromDataProvider->dataSetName();
        }

        return $title;
    }
}
