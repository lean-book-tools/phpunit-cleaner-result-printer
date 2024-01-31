<?php

declare(strict_types=1);

namespace LeanBookTools\Subscribers\TestRunner;

use LeanBookTools\Subscribers\AbstractSubscriber;
use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\TestRunner\StartedSubscriber;

final class TestRunnerStartedSubscriber extends AbstractSubscriber implements StartedSubscriber
{
    public function notify(Started $event): void
    {
        // starting message is printed by PHPUnit itself
        $this->simplePrinter->writeln('PHPUnit' . PHP_EOL);
    }
}
