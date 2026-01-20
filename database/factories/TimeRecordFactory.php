<?php

namespace Modules\Employee\Database\Factories;

use Modules\Employee\Models\TimeRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeRecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = TimeRecord::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}

