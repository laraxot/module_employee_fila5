<?php

declare(strict_types=1);

namespace Modules\Employee\Tests\Feature\Widgets;

use Carbon\Carbon;
use Livewire\Livewire;
use Modules\Employee\Filament\Widgets\EmployeeOverviewWidget;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\WorkHour;

/**
 * Test per il widget EmployeeOverviewWidget.
 */
describe('EmployeeOverviewWidget', function () {
    beforeEach(function () {
        // Setup base per ogni test
        Carbon::setTestNow('2025-01-06 10:00:00');
    });

    afterEach(function () {
        // Cleanup dopo ogni test
        Carbon::setTestNow();
    });

    test('displays total employees count correctly', function () {
        // Arrange
        Employee::factory()->count(5)->create();

        // Act & Assert
        Livewire::test(EmployeeOverviewWidget::class)->assertSee('Total Employees')->assertSee('5');
    });

    test('displays active employees today', function () {
        // Arrange
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();
        $employee3 = Employee::factory()->create();

        // Solo employee1 e employee2 hanno timbrature oggi
        WorkHour::factory()->create([
            'employee_id' => $employee1->id,
            'timestamp' => Carbon::today()->addHours(8),
            'type' => WorkHour::TYPE_CLOCK_IN,
        ]);

        WorkHour::factory()->create([
            'employee_id' => $employee2->id,
            'timestamp' => Carbon::today()->addHours(9),
            'type' => WorkHour::TYPE_CLOCK_IN,
        ]);

        // Act & Assert
        Livewire::test(EmployeeOverviewWidget::class)->assertSee('Active Today')->assertSee('2'); // Solo 2 dipendenti attivi
    });

    test('displays employees on leave', function () {
        // Arrange
        Employee::factory()->count(3)->create(['status' => 'active']);
        Employee::factory()->count(2)->create(['status' => 'on_leave']);

        // Act & Assert
        Livewire::test(EmployeeOverviewWidget::class)->assertSee('On Leave')->assertSee('2');
    });

    test('displays new employees this month', function () {
        // Arrange
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth();

        // 3 dipendenti questo mese
        Employee::factory()->count(3)->create(['created_at' => $thisMonth]);
        // 2 dipendenti il mese scorso
        Employee::factory()->count(2)->create(['created_at' => $lastMonth]);

        // Act & Assert
        Livewire::test(EmployeeOverviewWidget::class)->assertSee('New This Month')->assertSee('3');
    });

    test('widget uses caching for performance', function () {
        // Arrange
        Employee::factory()->count(10)->create();

        // Act - Prima chiamata
        $widget = Livewire::test(EmployeeOverviewWidget::class);

        // Verifica che la cache sia stata creata
        expect(cache()->has('employee.overview.stats'))->toBeTrue();

        // Assert - Seconda chiamata dovrebbe usare la cache
        $widget->assertSee('Total Employees')->assertSee('10');
    });

    test('displays appropriate colors based on values', function () {
        // Arrange - Nessun dipendente attivo oggi
        Employee::factory()->count(5)->create();
        // Nessuna timbratura oggi

        // Act
        $component = Livewire::test(EmployeeOverviewWidget::class);

        // Assert - Dovrebbe mostrare 0 dipendenti attivi
        $component->assertSee('Active Today')->assertSee('0');
    });

    test('chart data is generated correctly', function () {
        // Arrange
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates[] = $date;

            // Crea dipendenti con date diverse
            Employee::factory()->create(['created_at' => $date]);
        }

        // Act
        $widget = new EmployeeOverviewWidget();
        $stats = $widget->getStats();

        // Assert - Il primo stat dovrebbe avere chart data
        expect($stats[0]->getChart())->toBeArray();
        expect(count($stats[0]->getChart()))->toBe(7); // 7 giorni di dati
    });

    test('handles empty data gracefully', function () {
        // Arrange - Nessun dipendente nel database

        // Act & Assert
        Livewire::test(EmployeeOverviewWidget::class)
            ->assertSee('Total Employees')
            ->assertSee('0')
            ->assertSee('Active Today')
            ->assertSee('0');
    });
});
