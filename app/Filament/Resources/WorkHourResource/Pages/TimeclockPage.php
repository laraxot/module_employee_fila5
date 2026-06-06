<?php

declare(strict_types=1);

namespace Modules\Employee\Filament\Resources\WorkHourResource\Pages;

use Filament\Actions\Action;
use Modules\Employee\Filament\Resources\WorkHourResource;
use Modules\Xot\Filament\Resources\Pages\XotBasePage;
use Override;

class TimeclockPage extends XotBasePage
{
    protected static string $resource = WorkHourResource::class;

    protected string $view = 'employee::filament.pages.timeclock';

    protected static ?string $title = 'Time Clock';

    protected static ?string $navigationLabel = 'Time Clock';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_list')
                ->label('Back to Work Hours')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(function (): string {
                    $url = static::$resource::getUrl('index');

                    return is_string($url) ? $url : '';
                }),
        ];
    }

    #[Override]
    public function getViewData(): array
    {
        return [
            'title' => 'Employee Time Clock',
            'subtitle' => 'Track your work hours with a simple click',
        ];
    }
}
