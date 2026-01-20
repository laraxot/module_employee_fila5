<?php

declare(strict_types=1);

namespace Modules\Employee\Tests\Unit\Models;

use Carbon\Carbon;
use Modules\Employee\Models\User;
use Modules\Employee\Models\WorkHour;

beforeEach(function () {
    $this->employee = createEmployee([
        'employee_code' => 'EMP001',
        'personal_data' => [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birth_date' => '1990-01-15',
        ],
        'contact_data' => [
            'email' => 'john.doe@company.com',
            'phone' => '+1234567890',
            'address' => '123 Main St',
        ],
        'work_data' => [
            'hire_date' => '2023-01-01',
            'position' => 'Software Developer',
            'department' => 'IT',
        ],
        'status' => 'active',
    ]);
});

describe('Employee Model Business Logic', function () {
    test('employee can be created with required data', function () {
        expect($this->employee)->toBeEmployee();
        expect($this->employee->employee_code)->toBe('EMP001');
        expect($this->employee->status)->toBe('active');
    });

    test('employee personal data is properly cast to array', function () {
        expect($this->employee->personal_data)->toBeArray();
        expect($this->employee->personal_data['first_name'])->toBe('John');
        expect($this->employee->personal_data['last_name'])->toBe('Doe');
    });

    test('employee contact data is properly cast to array', function () {
        expect($this->employee->contact_data)->toBeArray();
        expect($this->employee->contact_data['email'])->toBe('john.doe@company.com');
        expect($this->employee->contact_data['phone'])->toBe('+1234567890');
    });

    test('employee work data is properly cast to array', function () {
        expect($this->employee->work_data)->toBeArray();
        expect($this->employee->work_data['hire_date'])->toBe('2023-01-01');
        expect($this->employee->work_data['position'])->toBe('Software Developer');
    });

    test('employee can have multiple work hour entries', function () {
        $today = Carbon::today();

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => $today->copy()->setTime(9, 0),
        ]);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_OUT,
            'timestamp' => $today->copy()->setTime(17, 0),
        ]);

        $entries = WorkHour::getTodayEntries($this->employee->id, $today);
        expect($entries)->toHaveCount(2);
    });

    test('employee salary data is properly secured', function () {
        $employee = createEmployee([
            'salary_data' => [
                'base_salary' => 50000,
                'currency' => 'USD',
                'pay_frequency' => 'monthly',
                'benefits' => ['health_insurance', 'dental'],
            ],
        ]);

        expect($employee->salary_data)->toBeArray();
        expect($employee->salary_data['base_salary'])->toBe(50000);
        expect($employee->salary_data['currency'])->toBe('USD');
        expect($employee->salary_data['benefits'])->toContain('health_insurance');
    });

    test('employee can have document attachments', function () {
        $employee = createEmployee([
            'documents' => [
                'resume' => 'path/to/resume.pdf',
                'contract' => 'path/to/contract.pdf',
                'id_document' => 'path/to/id.pdf',
            ],
        ]);

        expect($employee->documents)->toBeArray();
        expect($employee->documents['resume'])->toBe('path/to/resume.pdf');
        expect($employee->documents)->toHaveKey('contract');
    });

    test('employee can have profile photo url', function () {
        $employee = createEmployee([
            'photo_url' => 'https://example.com/photos/employee.jpg',
        ]);

        expect($employee->photo_url)->toBe('https://example.com/photos/employee.jpg');
    });

    test('employee uses correct table', function () {
        expect($this->employee->getTable())->toBe('users');
    });

    test('employee has proper fillable attributes', function () {
        $fillable = $this->employee->getFillable();

        expect($fillable)->toContain('employee_code');
        expect($fillable)->toContain('personal_data');
        expect($fillable)->toContain('contact_data');
        expect($fillable)->toContain('work_data');
        expect($fillable)->toContain('documents');
        expect($fillable)->toContain('status');
    });

    test('employee data arrays are properly cast', function () {
        $casts = $this->employee->getCasts();

        expect($casts)->toHaveKey('personal_data');
        expect($casts)->toHaveKey('contact_data');
        expect($casts)->toHaveKey('work_data');
        expect($casts)->toHaveKey('documents');
        expect($casts)->toHaveKey('salary_data');
    });

    test('employee can update personal data', function () {
        $this->employee->personal_data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'birth_date' => '1985-05-20',
        ];
        $this->employee->save();

        expect($this->employee->fresh()->personal_data['first_name'])->toBe('Jane');
        expect($this->employee->fresh()->personal_data['last_name'])->toBe('Smith');
    });

    test('employee can update contact information', function () {
        $this->employee->contact_data = [
            'email' => 'new.email@company.com',
            'phone' => '+0987654321',
            'address' => '456 New Street',
        ];
        $this->employee->save();

        expect($this->employee->fresh()->contact_data['email'])->toBe('new.email@company.com');
        expect($this->employee->fresh()->contact_data['phone'])->toBe('+0987654321');
    });

    test('employee can update work information', function () {
        $this->employee->work_data = [
            'position' => 'Senior Developer',
            'department' => 'Engineering',
            'manager' => 'Jane Manager',
        ];
        $this->employee->save();

        expect($this->employee->fresh()->work_data['position'])->toBe('Senior Developer');
        expect($this->employee->fresh()->work_data['department'])->toBe('Engineering');
    });

    test('employee can change status', function () {
        $this->employee->status = 'inactive';
        $this->employee->save();

        expect($this->employee->fresh()->status)->toBe('inactive');
    });

    test('employee inherits from user model correctly', function () {
        expect($this->employee)->toBeInstanceOf(User::class);
    });
});
