<?php

namespace App\Http\Services;

use App\Models\Alert;
use App\Models\Sensor;

class SensorService
{

    public function checkStatus(Sensor $sensor)
    {
        if ($sensor->status !== config("constants.STATUS_ALERT") && $sensor->lastMeasurement()->co2 >= 2000) {
            try {
                $sensor->status = config("constants.STATUS_WARN");
                $sensor->save();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        $lastMeasurements = $sensor->lastMeasurements();
        $highMeasurements = array_filter($lastMeasurements, function ($measurement) {
            return $measurement >= 2000;
        });
        $lowMeasurements = array_filter($lastMeasurements, function ($measurement) {
            return $measurement < 2000;
        });
        if (count($highMeasurements) >= 3) {
            try {
                $sensor->status = config("constants.STATUS_ALERT");
                $sensor->save();
            } catch (\Exception $e) {
                throw $e;
            }
        } else if (count($lowMeasurements) === 3) {
            try {
                $sensor->status = config("constants.STATUS_OK");
                $sensor->save();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        if ($sensor->status === config("constants.STATUS_ALERT")) {
            Alert::create(['uuid' => $sensor->uuid, 'measurement_id' => $sensor->lastMeasurement()->id]);
        }
    }
}
