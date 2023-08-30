<?php

declare(strict_types=1);

namespace DefectiveCode\Skeleton\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Carbon::setTestNow('1988-12-15 06:00:00');
    }
}
