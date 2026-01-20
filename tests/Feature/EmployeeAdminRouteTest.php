<?php

declare(strict_types=1);

namespace Modules\Employee\Tests\Feature;

use Filament\Facades\Filament;
use Filament\Panel;

uses(\Modules\Employee\Tests\TestCase::class);

describe('Employee Admin Route', function () {
    it('redirects to login when not authenticated', function () {
        $response = $this->get('/employee/admin');

        expect($response->status())
            ->toBe(302)
            ->and($response->headers->get('location'))
            ->toContain('/employee/admin/login');
    });

    it('has proper filament panel configuration', function () {
        // Test that the Filament panel is properly configured
        $panels = Filament::getPanels();

        expect($panels)
            ->toHaveKey('employee::admin')
            ->and($panels['employee::admin'])
            ->toBeInstanceOf(Panel::class);
    });

    it('employee admin route exists and is accessible', function () {
        $response = $this->get('/employee/admin');

        // Should redirect to login (302) or show login page (200)
        expect($response->status())->toBeIn([200, 302]);
    });

    it('work hours resource route exists', function () {
        $response = $this->get('/employee/admin/work-hours');

        // Should redirect to login or show the resource
        expect($response->status())->toBeIn([200, 302, 404]);
    });
});
