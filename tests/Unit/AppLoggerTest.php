<?php

namespace Tests\Unit;

use AppLogger;
use PHPUnit\Framework\TestCase;

class AppLoggerTest extends TestCase
{
    private AppLogger $logger;
    private string $logDir;
    private string $logFile;

    protected function setUp(): void
    {
        $this->logDir  = PROJECT_PATH . '/logs';
        $this->logFile = $this->logDir . '/app.log';

        // Remove any leftover log file so each test starts clean
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }

        $this->logger = new AppLogger();
    }

    protected function tearDown(): void
    {
        // Clean up the log file after each test
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    // -----------------------------------------------------------------
    // Basic logging
    // -----------------------------------------------------------------

    public function test_log_creates_log_file(): void
    {
        $this->logger->log('info', 'test_action');
        $this->assertFileExists($this->logFile);
    }

    public function test_log_directory_is_created(): void
    {
        $this->assertDirectoryExists($this->logDir);
    }

    // -----------------------------------------------------------------
    // Log entry format (JSON structure)
    // -----------------------------------------------------------------

    public function test_log_entry_is_valid_json(): void
    {
        $this->logger->log('info', 'test_action', ['key' => 'value']);

        $line = trim(file_get_contents($this->logFile));
        $entry = json_decode($line, true);

        $this->assertNotNull($entry, 'Log entry should be valid JSON');
    }

    public function test_log_entry_contains_required_fields(): void
    {
        $this->logger->log('warning', 'something_happened', ['detail' => 'abc']);

        $line = trim(file_get_contents($this->logFile));
        $entry = json_decode($line, true);

        $this->assertArrayHasKey('timestamp', $entry);
        $this->assertArrayHasKey('level', $entry);
        $this->assertArrayHasKey('action', $entry);
        $this->assertArrayHasKey('details', $entry);
        $this->assertArrayHasKey('ip_address', $entry);
        $this->assertArrayHasKey('user_id', $entry);
    }

    public function test_log_entry_stores_correct_level_and_action(): void
    {
        $this->logger->log('error', 'db_connection_failed', ['host' => 'localhost']);

        $line = trim(file_get_contents($this->logFile));
        $entry = json_decode($line, true);

        $this->assertSame('error', $entry['level']);
        $this->assertSame('db_connection_failed', $entry['action']);
        $this->assertSame(['host' => 'localhost'], $entry['details']);
    }

    // -----------------------------------------------------------------
    // Log levels
    // -----------------------------------------------------------------

    public function test_info_level(): void
    {
        $this->logger->log('info', 'info_action');

        $line = trim(file_get_contents($this->logFile));
        $entry = json_decode($line, true);

        $this->assertSame('info', $entry['level']);
    }

    public function test_warning_level(): void
    {
        $this->logger->log('warning', 'warning_action');

        $line = trim(file_get_contents($this->logFile));
        $entry = json_decode($line, true);

        $this->assertSame('warning', $entry['level']);
    }

    public function test_error_level(): void
    {
        $this->logger->log('error', 'error_action');

        $line = trim(file_get_contents($this->logFile));
        $entry = json_decode($line, true);

        $this->assertSame('error', $entry['level']);
    }

    // -----------------------------------------------------------------
    // Multiple log entries
    // -----------------------------------------------------------------

    public function test_multiple_entries_appended(): void
    {
        $this->logger->log('info', 'first');
        $this->logger->log('info', 'second');
        $this->logger->log('info', 'third');

        $lines = array_filter(explode(PHP_EOL, file_get_contents($this->logFile)));
        $this->assertCount(3, $lines);
    }

    // -----------------------------------------------------------------
    // Specialized logging methods
    // -----------------------------------------------------------------

    public function test_logAuth_includes_auth_category(): void
    {
        $this->logger->logAuth('login_success', ['username' => 'admin']);

        $line = trim(file_get_contents($this->logFile));
        $entry = json_decode($line, true);

        $this->assertSame('auth', $entry['category']);
        $this->assertSame('login_success', $entry['action']);
    }

    public function test_logDataChange_includes_entity_info(): void
    {
        $this->logger->logDataChange('create', 'artifact', 42, ['name' => 'New Item']);

        $line = trim(file_get_contents($this->logFile));
        $entry = json_decode($line, true);

        $this->assertSame('data_change', $entry['category']);
        $this->assertSame('create', $entry['action']);
        $this->assertSame('artifact', $entry['entity_type']);
        $this->assertSame(42, $entry['entity_id']);
    }

    public function test_logApiRequest_includes_endpoint(): void
    {
        $this->logger->logApiRequest('/api/artifacts', ['method' => 'GET']);

        $line = trim(file_get_contents($this->logFile));
        $entry = json_decode($line, true);

        $this->assertSame('api', $entry['category']);
        $this->assertSame('api_request', $entry['action']);
        $this->assertSame('/api/artifacts', $entry['endpoint']);
    }

    // -----------------------------------------------------------------
    // Timestamp format
    // -----------------------------------------------------------------

    public function test_timestamp_is_iso8601(): void
    {
        $this->logger->log('info', 'ts_test');

        $line = trim(file_get_contents($this->logFile));
        $entry = json_decode($line, true);

        // date('c') produces ISO 8601 format; verify it parses
        $parsed = \DateTime::createFromFormat(\DateTime::ATOM, $entry['timestamp']);
        $this->assertNotFalse($parsed, 'Timestamp should be valid ISO 8601');
    }
}
