<?php

declare(strict_types=1);

namespace Modules\Employee\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Schemas\Components\Component;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Employee\Models\WorkHour;
use Modules\Xot\Filament\Widgets\XotBaseWidget;
use Override;

class WorkHoursSummaryWidget extends XotBaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'employee::filament.widgets.work-hours-summary';

    public function getViewData(): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        /** @var int $employeeId */
        $employeeId = (int) (Auth::id() ?? 0);

        $workHours = WorkHour::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('timestamp', [$startOfWeek, $endOfWeek])
            ->get();

        // Calculate total worked hours by processing clock in/out pairs
        $totalHours = 0.0;
        $dailyHours = [];

        /** @var Collection<int, WorkHour> $workHours */
        $groupedByDate = $workHours->groupBy(fn (WorkHour $item): string => $item->timestamp->format('Y-m-d'));

        foreach ($groupedByDate as $date => $dayEntries) {
            $hoursForDay = WorkHour::calculateWorkedHours($employeeId, $dayEntries->first()?->timestamp);
            $dailyHours[$date] = $hoursForDay;
            $totalHours += $hoursForDay;
        }

        $daysWorked = count(array_filter($dailyHours, fn ($hours) => $hours > 0));
        $averageHoursPerDay = $daysWorked > 0 ? round($totalHours / $daysWorked, 2) : 0;

        return [
            'totalHours' => $totalHours,
            'daysWorked' => $daysWorked,
            'averageHoursPerDay' => $averageHoursPerDay,
            'workHours' => $groupedByDate,
            'startOfWeek' => $startOfWeek->format('M j'),
            'endOfWeek' => $endOfWeek->format('M j, Y'),
        ];
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clock';
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        $label = __('employee::work_hours.weekly_summary');

        return is_string($label) ? $label : 'Weekly Summary';
    }

    public static function getNavigationGroup(): ?string
    {
        $group = __('employee::navigation.work_hours');

        return is_string($group) ? $group : null;
    }

    /**
     * Get the form schema for the widget.
     *
     * @return array<int|string, Component>
     */
    #[Override]
    public function getFormSchema(): array
    {
        return [
            // No form fields needed for this widget
        ];
    }

    /**
     * Get the header widgets for the widget.
     *
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            // Add any header widgets if needed
        ];
    }

    /**
     * Get the form model for the widget.
     */
    #[Override]
    public function getFormModel(): string|Model|null
    {
        return null; // No model needed for this widget
    }

    /**
     * Get the form actions for the widget.
     *
     * @return array<int|string, Action>
     */
    #[Override]
    protected function getFormActions(): array
    {
        return [];
    }

    /**
     * Save the form data.
     */
    #[Override]
    public function save(): void
    {
        // No save action needed for this widget
    }
}
