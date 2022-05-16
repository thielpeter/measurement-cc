<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sensor extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'status'];

    /**
     * Get the measurements associated with the sensor.
     * @return HasMany
     */
    public function measurements()
    {
        return $this->hasMany(Measurement::class, 'uuid');
    }

    /**
     * Get the oldest alert measurement time associated with the sensor.
     * @return string
     */
    public function firstAlertTime()
    {
        return $this->alerts()->with('measurement')->get()->pluck('measurement.time')->first();
    }

    /**
     * Get the latest alert measurement time associated with the sensor.
     * @return string
     */
    public function lastAlertTime()
    {
        return $this->alerts()->with('measurement')->get()->pluck('measurement.time')->last();
    }

    /**
     * Get the alerts measurements associated with the sensor.
     * @return array
     */
    public function alertsMeasurements()
    {
        return $this->alerts()->with('measurement')->oldest()->get()->pluck('measurement.co2')->toArray();
    }

    /**
     * Get the alerts associated with the sensor.
     * @return HasMany
     */
    public function alerts()
    {
        return $this->hasMany(Alert::class, 'uuid');
    }

    /**
     * Get the average measurements of the last 30 days associated with the sensor.
     * @return int
     */
    public function avgLast30Days()
    {
        return $this->measurements()->where('created_at', '>', now()->subDays(30)->endOfDay())->pluck('co2')->avg();
    }

    /**
     * Get the max measurement of the last 30 days associated with the sensor.
     * @return int
     */
    public function maxLast30Days()
    {
        return $this->measurements()->where('created_at', '>', now()->subDays(30)->endOfDay())->pluck('co2')->max();
    }

    /**
     * Get the last measurement associated with the sensor.
     * @return Model|HasMany|object
     */
    public function lastMeasurement()
    {
        return $this->measurements()->latest()->first();
    }

    /**
     * Get the last 3 measurements associated with the sensor.
     * @return array
     */
    public function lastMeasurements()
    {
        return $this->measurements()->latest()->take(3)->get()->pluck('co2')->toArray();
    }
}
