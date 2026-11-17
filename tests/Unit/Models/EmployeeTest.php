<?php

declare(strict_types=1);

namespace Modules\Employee\Tests\Unit\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\Department;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\Position;
use Webmozart\Assert\Assert;

beforeEach(function (): void {
    DB::beginTransaction();
});

afterEach(function (): void {
    DB::rollBack();
});

/**
 * HR employee fixture (Parental STI on users + JSON payload).
 *
 * @param  array<string, mixed>  $overrides
 */
function employeeFixture(array $overrides = []): Employee
{
    /** @var array<string, mixed> $attributes */
    $attributes = array_merge([
        'personal_data' => [
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
        ],
        'contact_data' => [
            'email' => 'mario.rossi@example.com',
            'phone' => '+39 123 456 7890',
        ],
        'work_data' => [
            'employee_id' => 'EMP001',
            'hire_date' => '2024-01-01',
        ],
        'status' => 'attivo',
    ], $overrides);

    return createEmployee($attributes);
}

test('employee can be created', function (): void {
    $employee = employeeFixture();

    expect($employee->getKey())->toBeGreaterThan(0);
});

test('employee has fillable attributes', function (): void {
    $employee = employeeFixture();
    $fillable = $employee->getFillable();

    expect($fillable)->toContain('user_id');
    expect($fillable)->toContain('employee_code');
    expect($fillable)->toContain('personal_data');
    expect($fillable)->toContain('contact_data');
    expect($fillable)->toContain('work_data');
    expect($fillable)->toContain('status');
});

test('employee has casts defined', function (): void {
    $employee = employeeFixture();
    $casts = $employee->getCasts();

    expect($casts)->toHaveKey('personal_data');
    expect($casts)->toHaveKey('contact_data');
    expect($casts)->toHaveKey('work_data');
    expect($casts)->toHaveKey('documents');
    expect($casts)->toHaveKey('salary_data');
    expect($casts)->toHaveKey('created_at');
    expect($casts)->toHaveKey('updated_at');
});

test('employee uses users table via parental sti', function (): void {
    $employee = employeeFixture();

    expect($employee->getTable())->toBe('users');
});

test('employee belongs to department', function (): void {
    $employee = employeeFixture();
    $department = Department::factory()->create();
    Assert::isInstanceOf($department, Department::class);

    $employee->update(['department_id' => $department->id]);
    $employee->refresh();

    $related = $employee->department;
    Assert::isInstanceOf($related, Department::class);
    expect($related->id)->toBe($department->id);
});

test('employee belongs to position', function (): void {
    $employee = employeeFixture();
    $position = Position::factory()->create();
    Assert::isInstanceOf($position, Position::class);

    $employee->update(['position_id' => $position->id]);
    $employee->refresh();

    $related = $employee->position;
    Assert::isInstanceOf($related, Position::class);
    expect($related->id)->toBe($position->id);
});

test('employee can have manager', function (): void {
    $employee = employeeFixture();
    $manager = employeeFixture([
        'personal_data' => ['first_name' => 'Luigi', 'last_name' => 'Bianchi'],
        'contact_data' => ['email' => 'luigi.bianchi@example.com', 'phone' => '+39 000'],
        'work_data' => ['employee_id' => 'EMP002', 'hire_date' => '2023-01-01'],
    ]);

    $employee->update(['manager_id' => $manager->id]);
    $employee->refresh();

    $related = $employee->manager;
    Assert::isInstanceOf($related, Employee::class);
    expect($related->id)->toBe($manager->id);
});

test('employee can have subordinates', function (): void {
    $employee = employeeFixture();
    $subordinate = employeeFixture([
        'manager_id' => $employee->id,
        'personal_data' => ['first_name' => 'Anna', 'last_name' => 'Verdi'],
        'contact_data' => ['email' => 'anna.verdi@example.com', 'phone' => '+39 111'],
        'work_data' => ['employee_id' => 'EMP003', 'hire_date' => '2024-06-01'],
    ]);

    $employee->load('subordinates');
    $subordinates = $employee->subordinates;
    Assert::isInstanceOf($subordinates, Collection::class);
    expect($subordinates)->toHaveCount(1);

    $first = $subordinates->first();
    Assert::isInstanceOf($first, Employee::class);
    expect($first->id)->toBe($subordinate->id);
});

test('employee can get full name from personal data', function (): void {
    $employee = employeeFixture();

    expect($employee->full_name)->toBe('Mario Rossi');
});

test('employee can get email from contact data', function (): void {
    $employee = employeeFixture();

    expect($employee->email)->toBe('mario.rossi@example.com');
});

test('employee can get phone from contact data', function (): void {
    $employee = employeeFixture();

    expect($employee->phone)->toBe('+39 123 456 7890');
});

test('employee can check if active', function (): void {
    $employee = employeeFixture(['status' => 'attivo']);

    expect($employee->isActive())->toBeTrue();

    $employee->update(['status' => 'inattivo']);
    expect($employee->isActive())->toBeFalse();
});

test('employee can check if has manager', function (): void {
    $employee = employeeFixture();

    expect($employee->hasManager())->toBeFalse();

    $manager = employeeFixture([
        'personal_data' => ['first_name' => 'Luigi', 'last_name' => 'Bianchi'],
        'contact_data' => ['email' => 'luigi.bianchi@example.com', 'phone' => '+39 000'],
        'work_data' => ['employee_id' => 'EMP002', 'hire_date' => '2023-01-01'],
    ]);
    $employee->update(['manager_id' => $manager->id]);

    expect($employee->hasManager())->toBeTrue();
});

test('employee can check if has subordinates', function (): void {
    $employee = employeeFixture();

    expect($employee->hasSubordinates())->toBeFalse();

    employeeFixture([
        'manager_id' => $employee->id,
        'personal_data' => ['first_name' => 'Anna', 'last_name' => 'Verdi'],
        'contact_data' => ['email' => 'anna.verdi@example.com', 'phone' => '+39 111'],
        'work_data' => ['employee_id' => 'EMP003', 'hire_date' => '2024-06-01'],
    ]);

    expect($employee->hasSubordinates())->toBeTrue();
});

test('employee can be filtered by status', function (): void {
    $seed = employeeFixture(['status' => 'attivo']);
    $activeEmployee = employeeFixture([
        'status' => 'attivo',
        'personal_data' => ['first_name' => 'Active', 'last_name' => 'One'],
        'contact_data' => ['email' => 'active.one@example.com', 'phone' => '+39 222'],
        'work_data' => ['employee_id' => 'EMP-A', 'hire_date' => '2024-01-01'],
    ]);
    $inactiveEmployee = employeeFixture([
        'status' => 'inattivo',
        'personal_data' => ['first_name' => 'Inactive', 'last_name' => 'One'],
        'contact_data' => ['email' => 'inactive.one@example.com', 'phone' => '+39 333'],
        'work_data' => ['employee_id' => 'EMP-I', 'hire_date' => '2024-01-01'],
    ]);

    $activeEmployees = Employee::query()->active()->get();
    $inactiveEmployees = Employee::query()->inactive()->get();

    Assert::isInstanceOf($activeEmployees, Collection::class);
    Assert::isInstanceOf($inactiveEmployees, Collection::class);

    expect($activeEmployees)->toHaveCount(2);
    expect($inactiveEmployees)->toHaveCount(1);

    expect($activeEmployees->pluck('id'))->toContain($seed->id);
    expect($activeEmployees->pluck('id'))->toContain($activeEmployee->id);
    expect($inactiveEmployees->pluck('id'))->toContain($inactiveEmployee->id);
});

test('employee has work hours relationship', function (): void {
    $employee = employeeFixture();
    $relation = $employee->workHours();

    Assert::isInstanceOf($relation, HasMany::class);
    expect($relation->getForeignKeyName())->toBe('employee_id');
});

test('employee has leaves relationship', function (): void {
    $employee = employeeFixture();
    $relation = $employee->leaves();

    Assert::isInstanceOf($relation, HasMany::class);
    expect($relation->getForeignKeyName())->toBe('user_id');
});

test('employee stores documents as array cast not relation', function (): void {
    $employee = employeeFixture();
    $casts = $employee->getCasts();

    expect(array_key_exists('documents', $casts))->toBeTrue();
    expect($casts['documents'])->toBe('array');
});
