<?php

namespace Tests\Unit;

use RateLimiter;
use PHPUnit\Framework\TestCase;

/**
 * Lightweight tests for the RateLimiter class.
 *
 * Full behavioral tests require a MySQL/MariaDB connection.  These tests
 * verify that the class file can be loaded and that the constructor
 * signature is correct (i.e. it accepts a database connection parameter).
 */
class RateLimiterTest extends TestCase
{
    // -----------------------------------------------------------------
    // Smoke test: class exists and is loadable
    // -----------------------------------------------------------------

    public function test_rate_limiter_class_exists(): void
    {
        require_once PRIVATE_PATH . '/rate_limiter.php';
        $this->assertTrue(class_exists('RateLimiter'));
    }

    public function test_rate_limiter_has_expected_methods(): void
    {
        require_once PRIVATE_PATH . '/rate_limiter.php';

        $methods = get_class_methods('RateLimiter');

        $this->assertContains('recordAttempt', $methods);
        $this->assertContains('isRateLimited', $methods);
        $this->assertContains('checkAndRecord', $methods);
    }

    public function test_constructor_requires_db_parameter(): void
    {
        require_once PRIVATE_PATH . '/rate_limiter.php';

        $reflection = new \ReflectionClass('RateLimiter');
        $constructor = $reflection->getConstructor();
        $params = $constructor->getParameters();

        $this->assertCount(1, $params);
        $this->assertSame('db', $params[0]->getName());
    }
}
