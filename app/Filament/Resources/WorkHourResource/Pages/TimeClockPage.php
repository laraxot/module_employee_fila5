<?php

declare(strict_types=1);

namespace Modules\Employee\Filament\Resources\WorkHourResource\Pages;

use DateTimeInterface;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\Employee\Enums\WorkHourStatusEnum;
use Modules\Employee\Enums\WorkHourTypeEnum;
use Modules\Employee\Filament\Resources\WorkHourResource;
use Modules\Employee\Models\WorkHour;
use Modules\Xot\Filament\Resources\Pages\XotBasePage;
use Override;

/**
 * Class TimeClockPage
 *
 * Main page for employee time tracking and work hours management.
 * Displays the time clock widget and work hours list with filtering options.
 */
class TimeClockPage extends XotBasePage
{
    protected static string $resource = WorkHourResource::class;

    protected string $view = 'employee::filament.pages.work-hours';

    public ?array $filters = [
        'date_from' => null,
        'date_to' => null,
        'type' => null,
        'status' => null,
    ];

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)->schema([
                DatePicker::make('date_from')
                    ->label('From Date')
                    ->default(now()->startOfMonth())
                    ->maxDate(fn (callable $get) => $get('date_to') ?: now()),
                DatePicker::make('date_to')
                    ->label('To Date')
                    ->default(now()->endOfMonth())
                    ->minDate(fn (callable $get) => $get('date_from')),
                Select::make('type')
                    ->label('Type')
                    ->options(WorkHourTypeEnum::class)
                    ->placeholder('All Types'),
                Select::make('status')
                    ->label('Status')
                    ->options(WorkHourStatusEnum::class)
                    ->placeholder('All Statuses'),
            ]),
        ])->statePath('filters');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('timestamp')
                    ->label('Date & Time')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("employee::enums.work_hour_type.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        WorkHourTypeEnum::CLOCK_IN->value => 'success',
                        WorkHourTypeEnum::CLOCK_OUT->value => 'danger',
                        WorkHourTypeEnum::BREAK_START->value => 'warning',
                        WorkHourTypeEnum::BREAK_END->value => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("employee::enums.work_hour_status.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        WorkHourStatusEnum::PENDING->value => 'warning',
                        WorkHourStatusEnum::APPROVED->value => 'success',
                        WorkHourStatusEnum::REJECTED->value => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('location_name')->label('Location')->searchable(),
                TextColumn::make('notes')->label('Notes')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Additional filters can be added here
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (WorkHour $record): string => WorkHourResource::getUrl('edit', ['record' => $record])),
                Action::make('delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (WorkHour $record) => $record->delete())
                    ->visible(fn (WorkHour $record) => $record->status === WorkHourStatusEnum::PENDING->value),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->action(function () {
                            Notification::make()
                                ->title('Work hours deleted')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return WorkHour::query()
            ->where('employee_id', Auth::id())
            ->when($this->filters['date_from'] ?? null, function (Builder $query, mixed $date): Builder {
                if (! ($date instanceof DateTimeInterface || is_string($date))) {
                    return $query;
                }

                return $query->whereDate('timestamp', '>=', $date);
            })
            ->when($this->filters['date_to'] ?? null, function (Builder $query, mixed $date): Builder {
                if (! ($date instanceof DateTimeInterface || is_string($date))) {
                    return $query;
                }

                return $query->whereDate('timestamp', '<=', $date);
            })
            ->when($this->filters['type'] ?? null, fn (Builder $query, $type): Builder => $query->where('type', $type))
            ->when($this->filters['status'] ?? null, fn (Builder $query, $status): Builder => $query->where(
                'status',
                $status,
            ))
            ->latest('timestamp');
    }

    public function exportWorkHours(): void
    {
        $this->validate();

        // TODO: Implement export functionality

        Notification::make()
            ->title('Export started')
            ->body('Your work hours export has been queued. You will receive a notification when it is ready.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->action('exportWorkHours'),
            Action::make('back_to_list')
                ->label('Back to Dashboard')
                ->icon('heroicon-o-home')
                ->color('gray')
                ->url(route('filament.admin.pages.dashboard')),
        ];
    }

    #[Override]
    public function getViewData(): array
    {
        return [
            'currentTime' => now()->format('H:i:s'),
            'currentDate' => now()->translatedFormat('l, d F Y'),
            'workHoursToday' => $this->getWorkHoursToday(),
            'currentStatus' => $this->getCurrentStatus(),
        ];
    }

    protected function getWorkHoursToday(): string
    {
        $today = now()->startOfDay();

        $clockIn = WorkHour::query()
            ->where('employee_id', Auth::id())
            ->where('type', WorkHourTypeEnum::CLOCK_IN->value)
            ->whereDate('created_at', $today)
            ->first();

        if (! $clockIn) {
            return '00:00';
        }

        $clockOut = WorkHour::query()
            ->where('employee_id', Auth::id())
            ->where('type', WorkHourTypeEnum::CLOCK_OUT->value)
            ->whereDate('created_at', $today)
            ->first();

        $endTime = $clockOut->created_at ?? now();
        $totalMinutes = $endTime->diffInMinutes($clockIn->created_at);

        // Subtract break time if any
        $breakStart = WorkHour::query()
            ->where('employee_id', Auth::id())
            ->where('type', WorkHourTypeEnum::BREAK_START->value)
            ->whereDate('created_at', $today)
            ->first();

        if ($breakStart) {
            $breakEnd = WorkHour::query()
                ->where('employee_id', Auth::id())
                ->where('type', WorkHourTypeEnum::BREAK_END->value)
                ->whereDate('created_at', $today)
                ->first();

            if ($breakEnd) {
                $breakMinutes = $breakEnd->created_at?->diffInMinutes($breakStart->created_at) ?? 0;
                $totalMinutes -= $breakMinutes;
            }
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    protected function getCurrentStatus(): string
    {
        $lastAction = WorkHour::query()
            ->where('employee_id', Auth::id())
            ->latest('timestamp')
            ->first();

        if (! $lastAction) {
            return 'not_clocked_in';
        }

        return match ($lastAction->type) {
            WorkHourTypeEnum::CLOCK_IN->value => 'clocked_in',
            WorkHourTypeEnum::CLOCK_OUT->value => 'clocked_out',
            WorkHourTypeEnum::BREAK_START->value => 'on_break',
            WorkHourTypeEnum::BREAK_END->value => 'working',
            default => 'unknown',
        };
    }
}
