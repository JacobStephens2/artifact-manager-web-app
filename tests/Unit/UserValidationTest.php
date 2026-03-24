<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests for user validation in user_queries.php.
 *
 * Note: validate_user() calls has_unique_username() which requires a DB
 * connection.  All tests use a username shorter than 8 chars so that
 * validation fails on the length check before reaching the DB call.
 */
class UserValidationTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once PRIVATE_PATH . '/query_functions/user_queries.php';
    }

    // Short username avoids the has_unique_username() DB call
    private function baseUser(): array
    {
        return [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'username' => 'short',  // <8 chars, fails before DB check
            'password' => 'Str0ng!Pass99',
            'confirm_password' => 'Str0ng!Pass99',
        ];
    }

    // -----------------------------------------------------------------
    // First name
    // -----------------------------------------------------------------

    public function test_blank_first_name_returns_error(): void
    {
        $user = $this->baseUser();
        $user['first_name'] = '';
        $errors = validate_user($user);
        $firstNameErrors = array_filter($errors, fn($e) => str_contains($e, 'First name'));
        $this->assertNotEmpty($firstNameErrors);
    }

    public function test_short_first_name_returns_error(): void
    {
        $user = $this->baseUser();
        $user['first_name'] = 'A';
        $errors = validate_user($user);
        $firstNameErrors = array_filter($errors, fn($e) => str_contains($e, 'First name'));
        $this->assertNotEmpty($firstNameErrors);
    }

    // -----------------------------------------------------------------
    // Last name
    // -----------------------------------------------------------------

    public function test_blank_last_name_returns_error(): void
    {
        $user = $this->baseUser();
        $user['last_name'] = '';
        $errors = validate_user($user);
        $lastNameErrors = array_filter($errors, fn($e) => str_contains($e, 'Last name'));
        $this->assertNotEmpty($lastNameErrors);
    }

    // -----------------------------------------------------------------
    // Email
    // -----------------------------------------------------------------

    public function test_blank_email_returns_error(): void
    {
        $user = $this->baseUser();
        $user['email'] = '';
        $errors = validate_user($user);
        $emailErrors = array_filter($errors, fn($e) => str_contains($e, 'Email'));
        $this->assertNotEmpty($emailErrors);
    }

    public function test_invalid_email_returns_error(): void
    {
        $user = $this->baseUser();
        $user['email'] = 'not-an-email';
        $errors = validate_user($user);
        $emailErrors = array_filter($errors, fn($e) => str_contains($e, 'Email'));
        $this->assertNotEmpty($emailErrors);
    }

    // -----------------------------------------------------------------
    // Username (length checks only — DB uniqueness can't be unit-tested)
    // -----------------------------------------------------------------

    public function test_blank_username_returns_error(): void
    {
        $user = $this->baseUser();
        $user['username'] = '';
        $errors = validate_user($user);
        $usernameErrors = array_filter($errors, fn($e) => str_contains($e, 'Username'));
        $this->assertNotEmpty($usernameErrors);
    }

    public function test_short_username_returns_error(): void
    {
        $user = $this->baseUser();
        $user['username'] = 'abc';
        $errors = validate_user($user);
        $usernameErrors = array_filter($errors, fn($e) => str_contains($e, 'Username'));
        $this->assertNotEmpty($usernameErrors);
    }

    // -----------------------------------------------------------------
    // Password
    // -----------------------------------------------------------------

    public function test_blank_password_returns_error(): void
    {
        $user = $this->baseUser();
        $user['password'] = '';
        $errors = validate_user($user);
        $passwordErrors = array_filter($errors, fn($e) => str_contains($e, 'Password'));
        $this->assertNotEmpty($passwordErrors);
    }

    public function test_short_password_returns_error(): void
    {
        $user = $this->baseUser();
        $user['password'] = 'Short1!';
        $user['confirm_password'] = 'Short1!';
        $errors = validate_user($user);
        $passwordErrors = array_filter($errors, fn($e) => str_contains($e, '12'));
        $this->assertNotEmpty($passwordErrors);
    }

    public function test_password_without_uppercase_returns_error(): void
    {
        $user = $this->baseUser();
        $user['password'] = 'alllowercase1!';
        $user['confirm_password'] = 'alllowercase1!';
        $errors = validate_user($user);
        $passwordErrors = array_filter($errors, fn($e) => str_contains($e, 'uppercase'));
        $this->assertNotEmpty($passwordErrors);
    }

    public function test_password_without_lowercase_returns_error(): void
    {
        $user = $this->baseUser();
        $user['password'] = 'ALLUPPERCASE1!';
        $user['confirm_password'] = 'ALLUPPERCASE1!';
        $errors = validate_user($user);
        $passwordErrors = array_filter($errors, fn($e) => str_contains($e, 'lowercase'));
        $this->assertNotEmpty($passwordErrors);
    }

    public function test_password_without_number_returns_error(): void
    {
        $user = $this->baseUser();
        $user['password'] = 'NoNumbersHere!A';
        $user['confirm_password'] = 'NoNumbersHere!A';
        $errors = validate_user($user);
        $passwordErrors = array_filter($errors, fn($e) => str_contains($e, 'number'));
        $this->assertNotEmpty($passwordErrors);
    }

    public function test_password_without_symbol_returns_error(): void
    {
        $user = $this->baseUser();
        $user['password'] = 'NoSymbols1Here';
        $user['confirm_password'] = 'NoSymbols1Here';
        $errors = validate_user($user);
        $passwordErrors = array_filter($errors, fn($e) => str_contains($e, 'symbol'));
        $this->assertNotEmpty($passwordErrors);
    }

    public function test_password_mismatch_returns_error(): void
    {
        $user = $this->baseUser();
        $user['password'] = 'Str0ng!Pass99';
        $user['confirm_password'] = 'DifferentPass1!';
        $errors = validate_user($user);
        $passwordErrors = array_filter($errors, fn($e) => str_contains($e, 'match'));
        $this->assertNotEmpty($passwordErrors);
    }

    // -----------------------------------------------------------------
    // Password not required
    // -----------------------------------------------------------------

    public function test_password_not_required_skips_password_validation(): void
    {
        $user = $this->baseUser();
        $user['password'] = '';
        $user['confirm_password'] = '';
        $errors = validate_user($user, ['password_required' => false]);
        // Should have username error (short) but NO password errors
        $passwordErrors = array_filter($errors, fn($e) => str_contains($e, 'Password'));
        $this->assertEmpty($passwordErrors);
    }
}
