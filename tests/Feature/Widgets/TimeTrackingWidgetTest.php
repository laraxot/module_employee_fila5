<?php

declare(strict_types=1);

namespace Modules\Employee\Tests\Feature\Widgets;

use Carbon\Carbon;
use Livewire\Livewire;
use Modules\Employee\Filament\Widgets\TimeTrackingWidget;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\WorkHour;
use Modules\User\Models\User;

/**
 * Test per il widget TimeTrackingWidget.
 */
describe('TimeTrackingWidget', function () {
    beforeEach(function () {
        // Setup base per ogni test
        Carbon::setTestNow('2025-01-06 10:00:00');

        // Crea utente e dipendente di test
        $this->user = User::factory()->create();
        $this->employee = Employee::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        // Autentica l'utente
        $this->actingAs($this->user);
    });

    afterEach(function () {
        // Cleanup dopo ogni test
        Carbon::setTestNow();
    });

    test('displays current time correctly', function () {
        // Act & Assert
        Livewire::test(TimeTrackingWidget::class)->assertSee('10:00:00')->assertSee('06/01/2025'); // Ora corrente del test // Data corrente del test
    });

    test('shows not started status when no clock entries', function () {
        // Act & Assert
        Livewire::test(TimeTrackingWidget::class)
            ->assertSee('Giornata non iniziata')
            ->assertSee('Pronto per iniziare la giornata')
            ->assertSee('Clock In');
    });

    test('shows active status when clocked in', function () {
        // Arrange
        WorkHour::factory()->create([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => Carbon::today()->addHours(8),
        ]);

        // Act & Assert
        Livewire::test(TimeTrackingWidget::class)
            ->assertSee('Sessione attiva')
            ->assertSee('08:00') // Ora di inizio sessione
            ->assertSee('Clock Out');
    });

    test('shows completed status when clocked out', function () {
        // Arrange
        WorkHour::factory()->create([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => Carbon::today()->addHours(8),
        ]);

        WorkHour::factory()->create([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_OUT,
            'timestamp' => Carbon::today()->addHours(17),
        ]);

        // Act & Assert
        Livewire::test(TimeTrackingWidget::class)
            ->assertSee('Giornata completata')
            ->assertSee('clock_out') // Ultima azione
            ->assertSee('Clock In'); // Può iniziare una nuova sessione
    });

    test('can perform clock in action', function () {
        // Act
        Livewire::test(TimeTrackingWidget::class)->call('clockIn');

        // Assert
        expect(
            WorkHour::where('employee_id', $this->employee->id)
                ->where('type', WorkHour::TYPE_CLOCK_IN)
                ->whereDate('timestamp', today())
                ->exists(),
        )->toBeTrue();
    });

    test('can perform clock out action when clocked in', function () {
        // Arrange - Clock in first
        WorkHour::factory()->create([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => Carbon::today()->addHours(8),
        ]);

        // Act
        Livewire::test(TimeTrackingWidget::class)->call('clockOut');

        // Assert
        expect(
            WorkHour::where('employee_id', $this->employee->id)
                ->where('type', WorkHour::TYPE_CLOCK_OUT)
                ->whereDate('timestamp', today())
                ->exists(),
        )->toBeTrue();
    });

    test('cannot clock out when not clocked in', function () {
        // Act & Assert
        Livewire::test(TimeTrackingWidget::class)->call('clockOut')->assertDispatched('notify', fn ($event) => $event['type'] === 'warning' && str_contains($event['message'], 'Devi prima fare clock-in'));
    });

    test('can start break when clocked in', function () {
        // Arrange - Clock in first
        WorkHour::factory()->create([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => Carbon::today()->addHours(8),
        ]);

        // Act
        Livewire::test(TimeTrackingWidget::class)->call('startBreak');

        // Assert
        expect(
            WorkHour::where('employee_id', $this->employee->id)
                ->where('type', WorkHour::TYPE_BREAK_START)
                ->whereDate('timestamp', today())
                ->exists(),
        )->toBeTrue();
    });

    test('cannot start break when not clocked in', function () {
        // Act & Assert
        Livewire::test(TimeTrackingWidget::class)->call('startBreak')->assertDispatched('notify', fn ($event) => $event['type'] === 'warning' && str_contains($event['message'], 'Devi prima fare clock-in'));
    });

    test('calculates session duration correctly', function () {
        // Arrange - Clock in 2 hours ago
        $clockInTime = Carbon::now()->subHours(2);
        Carbon::setTestNow($clockInTime);

        WorkHour::factory()->create([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => $clockInTime,
        ]);

        // Reset time to current
        Carbon::setTestNow('2025-01-06 10:00:00');

        // Act & Assert
        Livewire::test(TimeTrackingWidget::class)->assertSee('02:00'); // 2 ore di durata
    });

    test('displays daily stats correctly', function () {
        // Arrange
        WorkHour::factory()->create([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => Carbon::today()->addHours(8),
        ]);

        WorkHour::factory()->create([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_OUT,
            'timestamp' => Carbon::today()->addHours(12),
        ]);

        WorkHour::factory()->create([
            'employee_id' => $this->employee->id,
            'type' => WorkHour::TYPE_CLOCK_IN,
            'timestamp' => Carbon::today()->addHours(13),
        ]);

        // Act & Assert
        Livewire::test(TimeTrackingWidget::class)
            ->assertSee('2') // 2 clock-ins
            ->assertSee('1') // 1 clock-out
            ->assertSee('Entrate')
            ->assertSee('Uscite');
    });

    test('updates data automatically with polling', function () {
        // Act
        $component = Livewire::test(TimeTrackingWidget::class);

        // Simulate polling call
        $component->call('poll');

        // Assert - Component should update without errors
        $component->assertSuccessful();
    });
});
