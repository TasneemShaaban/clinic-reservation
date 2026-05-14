<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Throwable;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'reservation_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'reservation_code',
        'patient_id',
        'doctor_id',
        'appointment_date',
        'reservation_date',
        'time_slot',
        'status',
        'patient_name',
        'patient_email',
        'patient_phone',
        'notes',
        'consultation_fee',
        'processing_fee',
        'location',
        'session_details',
        'prescription_info',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'reservation_date' => 'date',
        'consultation_fee' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getKeyName()
    {
        return self::keyColumn();
    }

    public static function keyColumn(): string
    {
        try {
            return Schema::hasColumn((new self())->getTable(), 'reservation_id') ? 'reservation_id' : 'id';
        } catch (Throwable) {
            return 'reservation_id';
        }
    }

    public static function dateColumn(): string
    {
        try {
            $reservation = new self();

            if (Schema::hasColumn($reservation->getTable(), 'appointment_date')) {
                return 'appointment_date';
            }

            if (Schema::hasColumn($reservation->getTable(), 'reservation_date')) {
                return 'reservation_date';
            }
        } catch (Throwable) {
            return 'appointment_date';
        }

        return 'appointment_date';
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', Doctor::keyColumn());
    }

    public function getReservationIdAttribute(mixed $value): mixed
    {
        return $value ?? $this->attributes['id'] ?? null;
    }

    public function getReservationDateAttribute(mixed $value): mixed
    {
        return $value ?? $this->getAttribute('appointment_date');
    }

    public function appointmentDateForApi(): ?string
    {
        $value = $this->getAttribute('appointment_date') ?? ($this->attributes['reservation_date'] ?? null);

        if (! $value) {
            return null;
        }

        return $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d')
            : Carbon::parse($value)->format('Y-m-d');
    }

    public function effectiveStatus(): string
    {
        $status = strtolower(trim((string) ($this->attributes['status'] ?? 'pending')));

        return $status !== '' ? $status : 'pending';
    }

    public function canBeCancelled(): bool
    {
        return ! in_array($this->effectiveStatus(), ['cancelled', 'completed'], true);
    }
}
