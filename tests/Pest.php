<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\WorkHour;
use Modules\Employee\Tests\TestCase;

// ✅ CONFIGURAZIONE CORRETTA PEST + DATABASE TRANSACTIONS
uses(TestCase::class, DatabaseTransactions::class)->in('Feature', 'Unit', 'Integration');

expect()->extend('toBeEmployee', fn () => $this->toBeInstanceOf(Employee::class));

expect()->extend('toBeWorkHour', fn () => $this->toBeInstanceOf(WorkHour::class));

function createEmployee(array $attributes = []): Employee
{
    return Employee::factory()->create($attributes);
}

function makeEmployee(array $attributes = []): Employee
{
    return Employee::factory()->make($attributes);
}

function createWorkHour(array $attributes = []): WorkHour
{
    return WorkHour::factory()->create($attributes);
}

function makeWorkHour(array $attributes = []): WorkHour
{
    return WorkHour::factory()->make($attributes);
}
