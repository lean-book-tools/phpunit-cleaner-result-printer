<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ErrorTest extends TestCase
{
    public function testErrored(): void
    {
        function_not_found();
    }
}
