<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = ['co2','time', 'uuid'];

    /**
     * Get the sensor associated with the measurement.
     */
    public function sensor()
    {
        return $this->hasOne(Sensor::class);
    }
}
