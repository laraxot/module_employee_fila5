<?php

declare(strict_types=1);

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\TechPlanner\Models\Profile;

/**
 * Class TimeEntry.
 *
 * @property int $id
 * @property int $employee_id
 * @property Carbon $clock_in
 * @property Carbon|null $clock_out
 * @property Carbon|null $break_start
 * @property Carbon|null $break_end
 * @property int $break_duration
 * @property float|null $total_hours
 * @property float|null $regular_hours
 * @property float|null $overtime_hours
 * @property array<mixed>|null $location_in
 * @property array<mixed>|null $location_out
 * @property array<mixed>|null $device_info
 * @property string|null $notes
 * @property string|null $employee_notes
 * @property string|null $supervisor_notes
 * @property string $status
 * @property int|null $approved_by
 * @property Carbon|null $approved_at
 * @property string|null $rejection_reason
 * @property array<mixed>|null $anomalies
 * @property-read Employee|null $approvedBy
 * @property-read Profile|null $creator
 * @property-read Employee|null $employee
 * @property-read Profile|null $updater
 *
 * @method static Builder<static>|TimeEntry forEmployee(int $employeeId)
 * @method static Builder<static>|TimeEntry newModelQuery()
 * @method static Builder<static>|TimeEntry newQuery()
 * @method static Builder<static>|TimeEntry pending()
 * @method static Builder<static>|TimeEntry query()
 * @method static Builder<static>|TimeEntry withAnomalies()
 *
 * @mixin \Eloquent
 */
final class TimeEntry extends BaseModel
{
    public const string STATUS_PENDING = 'pending';

    public const string STATUS_APPROVED = 'approved';

    public const string STATUS_AUTO_APPROVED = 'auto_approved';

    public const string STATUS_REJECTED = 'rejected';

    /** @var list<string> */
    protected $fillable = [
        'employee_id',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'break_duration',
        'total_hours',
        'regular_hours',
        'overtime_hours',
        'location_in',
        'location_out',
        'device_info',
        'notes',
        'employee_notes',
        'supervisor_notes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'anomalies',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'break_start' => 'datetime',
            'break_end' => 'datetime',
            'break_duration' => 'integer',
            'total_hours' => 'float',
            'regular_hours' => 'float',
            'overtime_hours' => 'float',
            'location_in' => 'array',
            'location_out' => 'array',
            'device_info' => 'array',
            'approved_at' => 'datetime',
            'anomalies' => 'array',
        ];
    }

    /**
     * Get the employee that owns this time entry.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the employee who approved this time entry.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Scope to get pending entries.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get entries for a specific employee.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope to get entries with anomalies.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeWithAnomalies(Builder $query): Builder
    {
        return $query->whereNotNull('anomalies');
    }

    /**
     * Calculate total hours worked.
     */
    public function calculateTotalHours(): float
    {
        if (! $this->clock_out) {
            return 0.0;
        }

        $totalMinutes = $this->clock_in->diffInMinutes($this->clock_out);
        $totalMinutes -= $this->break_duration;

        return round($totalMinutes / 60, 2);
    }

    /**
     * Check if entry has anomalies.
     */
    public function hasAnomalies(): bool
    {
        return (bool) $this->anomalies;
    }

    /**
     * Check if entry is approved.
     */
    public function isApproved(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_AUTO_APPROVED], strict: true);
    }

    /**
     * Check if entry is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if entry is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
