<?php
declare(strict_types=1);


use PHPUnit\Framework\TestCase;

final class FailedTest extends TestCase
{
    public function testFailed(): void
    {
        $this->assertTrue(false);
    }
}
