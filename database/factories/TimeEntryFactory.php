<?php

declare(strict_types=1);

namespace Modules\Employee\Database\Factories;

use Modules\Employee\Models\TimeEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = TimeEntry::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}

