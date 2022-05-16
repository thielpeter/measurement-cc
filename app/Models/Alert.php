<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'measurement_id'];

    /**
     * Get the sensor associated with the alert.
     */
    public function sensor()
    {
        return $this->hasOne(Sensor::class);
    }

    /**
     * Get the measurement associated with the alert.
     */
    public function measurement()
    {
        return $this->hasOne(Measurement::class, 'id', 'measurement_id');
    }
}
