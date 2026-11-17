<?php

declare(strict_types=1);

namespace Modules\Employee\Tests\Unit\Models;

use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\Department;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\Position;
use RuntimeException;

beforeEach(function (): void {
    DB::beginTransaction();
});

afterEach(function (): void {
    DB::rollBack();
});

beforeEach(function (): void {
    /** @var Employee $employee */
    $employee = Employee::factory()->create([
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
    ]);
    $this->employee = $employee;
});

test('employee can be created', function (): void {
    expect($this->employee->getKey())->toBeGreaterThan(0);
});

test('employee has fillable attributes', function (): void {
    $fillable = $this->employee->getFillable();

    expect($fillable)->toContain('user_id');
    expect($fillable)->toContain('employee_code');
    expect($fillable)->toContain('personal_data');
    expect($fillable)->toContain('contact_data');
    expect($fillable)->toContain('work_data');
    expect($fillable)->toContain('status');
});

test('employee has casts defined', function (): void {
    $casts = $this->employee->getCasts();

    expect($casts)->toHaveKey('personal_data');
    expect($casts)->toHaveKey('contact_data');
    expect($casts)->toHaveKey('work_data');
    expect($casts)->toHaveKey('documents');
    expect($casts)->toHaveKey('salary_data');
    expect($casts)->toHaveKey('created_at');
    expect($casts)->toHaveKey('updated_at');
});

test('employee uses users table via parental sti', function (): void {
    expect($this->employee->getTable())->toBe('users');
});

test('employee belongs to department', function (): void {
    /** @var Department $department */
    $department = Department::factory()->create();
    $this->employee->update(['department_id' => $department->id]);
    $this->employee->refresh();

    $related = $this->employee->department;
    expect($related)->toBeInstanceOf(Department::class);
    if (! $related instanceof Department) {
        throw new RuntimeException('Expected department relation');
    }
    expect($related->id)->toBe($department->id);
});

test('employee belongs to position', function (): void {
    /** @var Position $position */
    $position = Position::factory()->create();
    $this->employee->update(['position_id' => $position->id]);
    $this->employee->refresh();

    $related = $this->employee->position;
    expect($related)->toBeInstanceOf(Position::class);
    if (! $related instanceof Position) {
        throw new RuntimeException('Expected position relation');
    }
    expect($related->id)->toBe($position->id);
});

test('employee can have manager', function (): void {
    /** @var Employee $manager */
    $manager = Employee::factory()->create();
    $this->employee->update(['manager_id' => $manager->id]);
    $this->employee->refresh();

    $related = $this->employee->manager;
    expect($related)->toBeInstanceOf(Employee::class);
    if (! $related instanceof Employee) {
        throw new RuntimeException('Expected manager relation');
    }
    expect($related->id)->toBe($manager->id);
});

test('employee can have subordinates', function (): void {
    /** @var Employee $subordinate */
    $subordinate = Employee::factory()->create(['manager_id' => $this->employee->id]);

    $this->employee->load('subordinates');

    expect($this->employee->subordinates)->toHaveCount(1);
    $first = $this->employee->subordinates->first();
    if (! $first instanceof Employee) {
        throw new RuntimeException('Expected subordinate employee');
    }
    expect($first->id)->toBe($subordinate->id);
});

test('employee can get full name from personal data', function (): void {
    expect($this->employee->full_name)->toBe('Mario Rossi');
});

test('employee can get email from contact data', function (): void {
    expect($this->employee->email)->toBe('mario.rossi@example.com');
});

test('employee can get phone from contact data', function (): void {
    expect($this->employee->phone)->toBe('+39 123 456 7890');
});

test('employee can check if active', function (): void {
    expect($this->employee->isActive())->toBeTrue();

    $this->employee->update(['status' => 'inattivo']);
    expect($this->employee->isActive())->toBeFalse();
});

test('employee can check if has manager', function (): void {
    expect($this->employee->hasManager())->toBeFalse();

    /** @var Employee $manager */
    $manager = Employee::factory()->create();
    $this->employee->update(['manager_id' => $manager->id]);

    expect($this->employee->hasManager())->toBeTrue();
});

test('employee can check if has subordinates', function (): void {
    expect($this->employee->hasSubordinates())->toBeFalse();

    Employee::factory()->create(['manager_id' => $this->employee->id]);

    expect($this->employee->hasSubordinates())->toBeTrue();
});

test('employee can be filtered by status', function (): void {
    /** @var Employee $activeEmployee */
    $activeEmployee = Employee::factory()->create(['status' => 'attivo']);
    /** @var Employee $inactiveEmployee */
    $inactiveEmployee = Employee::factory()->create(['status' => 'inattivo']);

    $activeEmployees = Employee::query()->active()->get();
    $inactiveEmployees = Employee::query()->inactive()->get();

    expect($activeEmployees)->toHaveCount(2); // Including the one from beforeEach
    expect($inactiveEmployees)->toHaveCount(1);

    expect($activeEmployees->pluck('id'))->toContain($this->employee->id);
    expect($activeEmployees->pluck('id'))->toContain($activeEmployee->id);
    expect($inactiveEmployees->pluck('id'))->toContain($inactiveEmployee->id);
});

test('employee has work hours relationship', function (): void {
    expect($this->employee->workHours()->getForeignKeyName())->toBe('employee_id');
});

test('employee has leaves relationship', function (): void {
    expect($this->employee->leaves()->getForeignKeyName())->toBe('user_id');
});

test('employee stores documents as array cast not relation', function (): void {
    expect(array_key_exists('documents', $this->employee->getCasts()))->toBeTrue();
    expect($this->employee->getCasts()['documents'])->toBe('array');
});
