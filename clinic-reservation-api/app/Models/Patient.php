<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $table = 'patients';
    protected $primaryKey = 'patient_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'gender',
        'date_of_birth',
        'address',
        'medical_history',
        'blood_type',
        'allergies',
        'last_visit',
        'created_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_visit' => 'date',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'patient_id', 'patient_id');
    }
}
