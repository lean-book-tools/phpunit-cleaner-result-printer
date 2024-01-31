<?php

declare(strict_types=1);

namespace DemoProject\Tests;

use PHPUnit\Framework\TestCase;

final class MultipleFailedTest extends TestCase
{
    public function testFailed1(): void
    {
        $this->assertTrue(false);
    }

    public function testFailed2(): void
    {
        $this->assertTrue(false);
    }
}
