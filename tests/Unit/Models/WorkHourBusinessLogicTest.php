<?php

declare(strict_types=1);

namespace Modules\Employee\Tests\Unit\Models;

use Carbon\Carbon;
use Modules\Employee\Models\WorkHour;

beforeEach(function () {
    $this->employee = createEmployee();
    $this->today = Carbon::today();
});

describe('WorkHour Business Logic Validation', function () {
    test('work hour constants are properly defined', function () {
        expect(WorkHour::TYPE_CLOCK_IN)->toBe('clock_in');
        expect(WorkHour::TYPE_CLOCK_OUT)->toBe('clock_out');
        expect(WorkHour::TYPE_BREAK_START)->toBe('break_start');
        expect(WorkHour::TYPE_BREAK_END)->toBe('break_end');

        expect(WorkHour::STATUS_PENDING)->toBe('pending');
        expect(WorkHour::STATUS_APPROVED)->toBe('approved');
        expect(WorkHour::STATUS_REJECTED)->toBe('rejected');
    });

    test('work hour type arrays contain all types', function () {
        expect(WorkHour::TYPES)->toContain(WorkHour::TYPE_CLOCK_IN);
        expect(WorkHour::TYPES)->toContain(WorkHour::TYPE_CLOCK_OUT);
        expect(WorkHour::TYPES)->toContain(WorkHour::TYPE_BREAK_START);
        expect(WorkHour::TYPES)->toContain(WorkHour::TYPE_BREAK_END);

        expect(WorkHour::STATUSES)->toContain(WorkHour::STATUS_PENDING);
        expect(WorkHour::STATUSES)->toContain(WorkHour::STATUS_APPROVED);
        expect(WorkHour::STATUSES)->toContain(WorkHour::STATUS_REJECTED);
    });

    test('work hour type check methods work correctly', function () {
        $clockIn = makeWorkHour(['type' => WorkHour::TYPE_CLOCK_IN]);
        $clockOut = makeWorkHour(['type' => WorkHour::TYPE_CLOCK_OUT]);
        $breakStart = makeWorkHour(['type' => WorkHour::TYPE_BREAK_START]);
        $breakEnd = makeWorkHour(['type' => WorkHour::TYPE_BREAK_END]);

        expect($clockIn->isClockIn())->toBeTrue();
        expect($clockIn->isClockOut())->toBeFalse();
        expect($clockIn->isBreakStart())->toBeFalse();
        expect($clockIn->isBreakEnd())->toBeFalse();

        expect($clockOut->isClockOut())->toBeTrue();
        expect($clockOut->isClockIn())->toBeFalse();

        expect($breakStart->isBreakStart())->toBeTrue();
        expect($breakStart->isClockIn())->toBeFalse();

        expect($breakEnd->isBreakEnd())->toBeTrue();
        expect($breakEnd->isClockIn())->toBeFalse();
    });

    test('work hour scopes filter correctly', function () {
        $employee2 = createEmployee();

        $workHour1 = createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => $this->today->copy()->setTime(9, 0),
        ]);

        $workHour2 = createWorkHour([
            'employee_id' => $employee2->id,
            'type' => WorkHour::TYPE_CLOCK_OUT,
            'timestamp' => $this->today->copy()->setTime(17, 0),
        ]);

        $employeeWorkHours = WorkHour::forEmployee($this->employee->id)->get();
        $clockInWorkHours = WorkHour::ofType(WorkHour::TYPE_CLOCK_IN)->get();

        expect($employeeWorkHours)->toHaveCount(1);
        expect($employeeWorkHours->first()->id)->toBe($workHour1->id);

        expect($clockInWorkHours)->toHaveCount(1);
        expect($clockInWorkHours->first()->id)->toBe($workHour1->id);
    });

    test('work hour today scope filters by date', function () {
        Carbon::setTestNow($this->today->copy()->setTime(12, 0));

        $todayWorkHour = createWorkHour([
            'employee_id' => $this->employee->id,
            'timestamp' => $this->today->copy()->setTime(9, 0),
        ]);

        $yesterdayWorkHour = createWorkHour([
            'employee_id' => $this->employee->id,
            'timestamp' => $this->today->copy()->subDay()->setTime(9, 0),
        ]);

        $todayWorkHours = WorkHour::today()->get();

        expect($todayWorkHours)->toHaveCount(1);
        expect($todayWorkHours->first()->id)->toBe($todayWorkHour->id);

        Carbon::setTestNow();
    });

    test('work hour formatted attributes work correctly', function () {
        $workHour = createWorkHour([
            'employee_id' => $this->employee->id,
            'timestamp' => Carbon::create(2024, 3, 15, 14, 30, 45),
        ]);

        expect($workHour->formatted_time)->toBe('14:30:45');
        expect($workHour->formatted_date)->toBe('15/03/2024');
        expect($workHour->formatted_date_time)->toBe('15/03/2024 14:30:45');
    });

    test('work hour static methods handle edge cases', function () {
        expect(WorkHour::getLastEntryForEmployee(999, $this->today))->toBeNull();
        expect(WorkHour::calculateWorkedHours(999, $this->today))->toBe(0.0);
        expect(WorkHour::getCurrentStatus(999, $this->today))->toBe('not_clocked_in');
        expect(WorkHour::getNextAction(999, $this->today))->toBe(WorkHour::TYPE_CLOCK_IN);
    });

    test('work hour calculates complex break scenarios', function () {
        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => $this->today->copy()->setTime(9, 0),
        ]);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_BREAK_START,
            'timestamp' => $this->today->copy()->setTime(10, 0),
        ]);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_BREAK_END,
            'timestamp' => $this->today->copy()->setTime(10, 30),
        ]);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_BREAK_START,
            'timestamp' => $this->today->copy()->setTime(12, 0),
        ]);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_BREAK_END,
            'timestamp' => $this->today->copy()->setTime(13, 0),
        ]);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_OUT,
            'timestamp' => $this->today->copy()->setTime(17, 0),
        ]);

        $workedHours = WorkHour::calculateWorkedHours($this->employee->id, $this->today);

        expect($workedHours)->toBe(6.5);
    });

    test('work hour handles incomplete sequences', function () {
        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => $this->today->copy()->setTime(9, 0),
        ]);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_BREAK_START,
            'timestamp' => $this->today->copy()->setTime(12, 0),
        ]);

        expect(WorkHour::getCurrentStatus($this->employee->id, $this->today))->toBe('on_break');
        expect(WorkHour::getNextAction($this->employee->id, $this->today))->toBe(WorkHour::TYPE_BREAK_END);
    });

    test('work hour next action logic follows state machine', function () {
        expect(WorkHour::getNextAction($this->employee->id, $this->today))->toBe(WorkHour::TYPE_CLOCK_IN);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => $this->today->copy()->setTime(9, 0),
        ]);

        expect(WorkHour::getNextAction($this->employee->id, $this->today))->toBe(WorkHour::TYPE_BREAK_START);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_BREAK_START,
            'timestamp' => $this->today->copy()->setTime(12, 0),
        ]);

        expect(WorkHour::getNextAction($this->employee->id, $this->today))->toBe(WorkHour::TYPE_BREAK_END);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_BREAK_END,
            'timestamp' => $this->today->copy()->setTime(13, 0),
        ]);

        expect(WorkHour::getNextAction($this->employee->id, $this->today))->toBe(WorkHour::TYPE_CLOCK_OUT);

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_OUT,
            'timestamp' => $this->today->copy()->setTime(17, 0),
        ]);

        expect(WorkHour::getNextAction($this->employee->id, $this->today))->toBe(WorkHour::TYPE_CLOCK_IN);
    });

    test('work hour handles default case in next action', function () {
        $workHour = createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => 'invalid_type',
            'timestamp' => $this->today->copy()->setTime(9, 0),
        ]);

        expect(WorkHour::getNextAction($this->employee->id, $this->today))->toBe(WorkHour::TYPE_CLOCK_IN);
    });

    test('work hour validation prevents invalid sequences', function () {
        expect(WorkHour::isValidNextEntry($this->employee->id, WorkHour::TYPE_BREAK_END))->toBeFalse();
        expect(WorkHour::isValidNextEntry($this->employee->id, WorkHour::TYPE_CLOCK_OUT))->toBeFalse();

        createWorkHour([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => $this->today->copy()->setTime(9, 0),
        ]);

        expect(WorkHour::isValidNextEntry($this->employee->id, WorkHour::TYPE_CLOCK_IN))->toBeFalse();
        expect(WorkHour::isValidNextEntry($this->employee->id, WorkHour::TYPE_BREAK_END))->toBeFalse();
        expect(WorkHour::isValidNextEntry($this->employee->id, WorkHour::TYPE_BREAK_START))->toBeTrue();
    });
});
