<?php

declare(strict_types=1);

namespace Modules\Employee\Filament\Resources\WorkHourResource\Pages;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Modules\Employee\Enums\WorkHourStatusEnum;
use Modules\Employee\Filament\Resources\WorkHourResource;
use Modules\Employee\Models\WorkHour;
use Modules\Xot\Filament\Resources\Pages\XotBaseCreateRecord;

class CreateWorkHour extends XotBaseCreateRecord
{
    protected static string $resource = WorkHourResource::class;

    protected function getRedirectUrl(): string
    {
        /** @var string */
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default status if not provided
        if (! isset($data['status'])) {
            $data['status'] = WorkHourStatusEnum::PENDING->value;
        }

        return $data;
    }

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

<<<<<<< .merge_file_MlY1UH
=======
<<<<<<< .merge_file_vLGPvf
>>>>>>> .merge_file_YkUn0R
        $timestampRaw = $data['timestamp'] ?? null;
        $timestamp = Carbon::parse(is_string($timestampRaw) ? $timestampRaw : 'now');
        $employeeIdRaw = $data['employee_id'] ?? null;
        $employeeId = is_int($employeeIdRaw) ? $employeeIdRaw : 0;
<<<<<<< .merge_file_MlY1UH
=======
=======
        $timestamp = Carbon::parse((string) ($data['timestamp'] ?? ''));
        $employeeId = (int) ($data['employee_id'] ?? 0);
>>>>>>> .merge_file_2eYHTm
>>>>>>> .merge_file_YkUn0R

        $existingEntry = WorkHour::query()
            ->where('employee_id', $employeeId)
            ->where('timestamp', $timestamp)
            ->where('type', $data['type'])
            ->first();

        if ($existingEntry) {
            Notification::make()
                ->title('Duplicate Entry')
                ->body('An entry with the same timestamp and type already exists for this employee.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Work hour entry created successfully';
    }
}
