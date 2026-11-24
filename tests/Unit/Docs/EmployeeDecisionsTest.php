<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;
use function Safe\glob;

uses(TestCase::class);

/*
 * Sensori source-level degli ADR del modulo (docs/adr/): nessuna app, nessun database.
 */

function employeeModuleSource(string $relative): string
{
    return file_get_contents(dirname(__DIR__, 3).'/'.$relative);
}

it('keeps Employee a Parental child of User on the users table', function (): void {
    $source = employeeModuleSource('app/Models/Employee.php');

    Assert::assertStringContainsString('class Employee extends User', $source);
    Assert::assertStringContainsString('use HasParent;', $source);
    Assert::assertStringContainsString("protected \$table = 'users';", $source);
})->group('employee-parental-child-of-user');

it('stores user ids as 36-char strings without a cross-database foreign key', function (): void {
    $migrations = [
        'database/migrations/2026_07_24_000001_create_absence_requests_table.php' => ['user_id', 'decided_by_user_id'],
        'database/migrations/2026_07_24_000004_create_time_records_table.php' => ['user_id'],
    ];

    foreach ($migrations as $file => $columns) {
        $source = employeeModuleSource($file);
        foreach ($columns as $column) {
            Assert::assertStringContainsString("\$table->string('{$column}', 36)", $source, $file.': '.$column);
            Assert::assertStringNotContainsString("foreign('{$column}')", $source, $file.': '.$column);
            Assert::assertStringNotContainsString("foreignId('{$column}')", $source, $file.': '.$column);
        }
    }
})->group('employee-user-id-string-columns');

it('keeps domain vocabularies in enums, not in model constants', function (): void {
    $offenders = [];
    foreach (glob(dirname(__DIR__, 3).'/app/Models/*.php') as $path) {
        if (is_string($path) && preg_match('/\bconst\s+(STATUS|TYPE)_/', file_get_contents($path)) === 1) {
            $offenders[] = basename($path);
        }
    }

    Assert::assertSame([], $offenders);
})->group('employee-domain-enums');
