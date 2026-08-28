<?php

declare(strict_types=1);

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\TechPlanner\Models\Profile;

/**
 * Class Position.
 *
 * @property-read Profile|null $creator
 * @property-read Collection<int, Employee> $employees
 * @property-read int|null $employees_count
 * @property-read Profile|null $updater
 *
 * @method static Builder<static>|Position newModelQuery()
 * @method static Builder<static>|Position newQuery()
 * @method static Builder<static>|Position query()
 *
 * @mixin \Eloquent
 */
class Position extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'department',
        'level',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the employees for the position.
     *
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
