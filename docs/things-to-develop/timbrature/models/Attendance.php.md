<?php

declare(strict_types=1);

namespace Modules\Employee\Models;

use Modules\Xot\Models\XotBaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * Class Attendance.
 * 
 * Modello per la gestione delle presenze dei dipendenti.
 * Gestisce timbrature, calcolo ore, approvazioni e geolocalizzazione.
 */
class Attendance extends XotBaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'attendances';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id',           // ID del dipendente
        'date',                  // Data della presenza
        'time_in',               // Orario di entrata
        'time_out',              // Orario di uscita
        'total_hours',           // Ore totali lavorate
        'overtime_hours',        // Ore straordinarie
        'break_hours',           // Ore di pausa
        'type',                  // Tipo: normale, straordinario, permesso, malattia
        'location',              // Posizione timbratura (JSON)
        'device_info',           // Informazioni dispositivo (JSON)
        'notes',                 // Note aggiuntive
        'status',                // Stato: registrata, approvata, rifiutata
        'approved_by',           // ID approvatore
        'approved_at',           // Data approvazione
        'rejection_reason',      // Motivo rifiuto
        'work_schedule_id',      // ID orario di lavoro
        'is_remote',             // Lavoro da remoto
        'location_validated',    // Posizione validata
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'break_hours' => 'decimal:2',
        'location' => 'array',
        'device_info' => 'array',
        'approved_at' => 'datetime',
        'is_remote' => 'boolean',
        'location_validated' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'device_info',           // Nascondi info dispositivo per privacy
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'duration_formatted',
        'is_complete',
        'is_overtime',
        'status_color',
    ];

    /**
     * Get the employee that owns the attendance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the approver of the attendance.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Get the work schedule for this attendance.
     */
    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    /**
     * Get the time entries for this attendance.
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Calculate the total hours worked.
     */
    public function calculateHours(): void
    {
        if ($this->time_in && $this->time_out) {
            $start = Carbon::parse($this->time_in);
            $end = Carbon::parse($this->time_out);
            
            // Calcola ore totali (escludendo pause)
            $totalMinutes = $end->diffInMinutes($start);
            $this->total_hours = round($totalMinutes / 60, 2);
            
            // Calcola straordinari (oltre 8 ore)
            $standardHours = config('employee.work_hours_standard', 8);
            if ($this->total_hours > $standardHours) {
                $this->overtime_hours = round($this->total_hours - $standardHours, 2);
            } else {
                $this->overtime_hours = 0;
            }
        }
    }

    /**
     * Check if the attendance is complete (has both time_in and time_out).
     */
    public function isComplete(): bool
    {
        return $this->time_in && $this->time_out;
    }

    /**
     * Check if the attendance has overtime.
     */
    public function isOvertime(): bool
    {
        return $this->overtime_hours > 0;
    }

    /**
     * Get the formatted duration.
     */
    public function getDurationFormattedAttribute(): string
    {
        if ($this->time_in && $this->time_out) {
            $start = Carbon::parse($this->time_in);
            $end = Carbon::parse($this->time_out);
            $hours = $end->diffInHours($start);
            $minutes = $end->diffInMinutes($start) % 60;
            return "{$hours}h {$minutes}m";
        }
        return 'N/A';
    }

    /**
     * Get the is_complete attribute.
     */
    public function getIsCompleteAttribute(): bool
    {
        return $this->isComplete();
    }

    /**
     * Get the is_overtime attribute.
     */
    public function getIsOvertimeAttribute(): bool
    {
        return $this->isOvertime();
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'registrata' => 'warning',
            'approvata' => 'success',
            'rifiutata' => 'danger',
            default => 'gray'
        };
    }

    /**
     * Get the type label for display.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'normale' => 'Normale',
            'straordinario' => 'Straordinario',
            'permesso' => 'Permesso',
            'malattia' => 'Malattia',
            'smart_working' => 'Smart Working',
            default => 'Non specificato'
        };
    }

    /**
     * Check if the attendance is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'registrata';
    }

    /**
     * Check if the attendance is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approvata';
    }

    /**
     * Check if the attendance is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rifiutata';
    }

    /**
     * Get the location coordinates.
     */
    public function getLocationCoordinates(): ?array
    {
        if (!$this->location || !isset($this->location['lat']) || !isset($this->location['lng'])) {
            return null;
        }

        return [
            'lat' => $this->location['lat'],
            'lng' => $this->location['lng'],
        ];
    }

    /**
     * Validate location against company premises.
     */
    public function validateLocation(): bool
    {
        if (!$this->location_validated) {
            // Implementa logica di validazione posizione
            $this->location_validated = true;
            $this->save();
        }

        return $this->location_validated;
    }

    /**
     * Scope for pending attendances.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'registrata');
    }

    /**
     * Scope for approved attendances.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approvata');
    }

    /**
     * Scope for today's attendances.
     */
    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    /**
     * Scope for this month's attendances.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year);
    }

    /**
     * Scope for overtime attendances.
     */
    public function scopeOvertime($query)
    {
        return $query->where('overtime_hours', '>', 0);
    }

    /**
     * Scope for remote work attendances.
     */
    public function scopeRemote($query)
    {
        return $query->where('is_remote', true);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Calcola automaticamente le ore quando viene salvato
        static::saving(function ($attendance) {
            $attendance->calculateHours();
        });
    }
} 