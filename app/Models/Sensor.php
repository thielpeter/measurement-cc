<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    /**
     * Get the measurements associated with the sensor.
     */
    public function metrics()
    {
        return $this->hasMany(Measurement::class);
    }

    /**
     * Get the alerts associated with the sensor.
     */
    public function alerts()
    {
        return $this->hasMany(Alerts::class);
    }

    /**
     * @return int
     */
    public function average()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function maximum()
    {
        return 0;
    }
}
