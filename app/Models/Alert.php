<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    /**
     * Get the sensor associated with the alert.
     */
    public function sensor()
    {
        return $this->hasOne(Sensor::class);
    }
}
