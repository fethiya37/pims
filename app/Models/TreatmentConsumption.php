<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentConsumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'location_id',
        'doctor_id',
        'treatment_date',
        'diagnosis',
        'notes',
        'status',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function items()
    {
        return $this->hasMany(TreatmentConsumptionItem::class);
    }
}