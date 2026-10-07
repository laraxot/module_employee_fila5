<?php

declare(strict_types=1);

namespace Modules\Employee\Filament\Resources\AbsenceRequestResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Modules\Employee\Models\AbsenceRequest;
use Modules\Xot\Filament\Resources\Schemas\XotBaseResourceForm;

class AbsenceRequestForm extends XotBaseResourceForm
{
    /**
     * @return array<string, Component>
     */
    public function getFormSchema(): array
    {
        return [
            'section' => Section::make(__('employee::absence_request.fields.section'))
                ->schema([
                    'user_id' => Select::make('user_id')
                        ->label(__('employee::absence_request.fields.user'))
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),

                    'type' => Select::make('type')
                        ->label(__('employee::absence_request.fields.type'))
                        ->options([
                            \Modules\Employee\Enums\AbsenceRequestTypeEnum::VACATION->value => __('employee::absence_request.types.vacation'),
                            \Modules\Employee\Enums\AbsenceRequestTypeEnum::LEAVE->value => __('employee::absence_request.types.leave'),
                            \Modules\Employee\Enums\AbsenceRequestTypeEnum::SICK->value => __('employee::absence_request.types.sick'),
                            \Modules\Employee\Enums\AbsenceRequestTypeEnum::INJURY->value => __('employee::absence_request.types.injury'),
                        ])
                        ->required(),

                    'status' => Select::make('status')
                        ->label(__('employee::absence_request.fields.status'))
                        ->options([
                            \Modules\Employee\Enums\AbsenceRequestStatusEnum::PENDING->value => __('employee::absence_request.statuses.pending'),
                            \Modules\Employee\Enums\AbsenceRequestStatusEnum::APPROVED->value => __('employee::absence_request.statuses.approved'),
                            \Modules\Employee\Enums\AbsenceRequestStatusEnum::REJECTED->value => __('employee::absence_request.statuses.rejected'),
                        ])
                        ->default(\Modules\Employee\Enums\AbsenceRequestStatusEnum::PENDING->value)
                        ->required(),

                    'starts_at' => DateTimePicker::make('starts_at')
                        ->label(__('employee::absence_request.fields.starts_at'))
                        ->required(),

                    'ends_at' => DateTimePicker::make('ends_at')
                        ->label(__('employee::absence_request.fields.ends_at'))
                        ->required(),

                    'notes' => Textarea::make('notes')
                        ->label(__('employee::absence_request.fields.notes'))
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }
}
