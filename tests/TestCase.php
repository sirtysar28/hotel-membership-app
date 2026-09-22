<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // PHP 8.5: konstanta PDO::MYSQL_ATTR_SSL_* deprecated di driver lama —
        // tidak memengaruhi fungsionalitas, cukup redam agar test fokus pada asersi.
        error_reporting(E_ALL & ~E_DEPRECATED);

        parent::setUp();
    }
}
