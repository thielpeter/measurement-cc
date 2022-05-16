<?php

namespace App\Http\Controllers;


use App\Http\Requests\PostMeasurementRequest;
use App\Models\Alert;
use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SensorsController extends Controller
{
    private $alertsController;

    private static $STATUS_OK = 'OK';
    private static $STATUS_WARN = 'WARN';
    private static $STATUS_ALERT = 'ALERT';

    public function __construct(AlertsController $alertsController)
    {
        $this->alertsController = $alertsController;
    }

    /**
     * @param $uuid
     * @return JsonResponse
     */
    public function getStatus($uuid)
    {
        return response()->json(Sensor::where(['uuid' => $uuid])->status);
    }

    /**
     * @param $uuid
     * @return JsonResponse
     */
    public function getMetrics($uuid)
    {
        $sensor = Sensor::where(['uuid' => $uuid])->first();
        if ($sensor) {
            $maxLast30Days = $sensor->maxLast30Days();
            $avgLast30Days = $sensor->avgLast30Days();

            return response()->json([
                'maxLast30Days' => $maxLast30Days,
                'avgLast30Days' => $avgLast30Days,
            ]);
        }
        return response()->json('No metrics found', 404);
    }

    /**
     * @param $uuid
     * @return JsonResponse
     */
    public function getAlerts($uuid)
    {
        $sensor = Sensor::where(['uuid' => $uuid])->first();
        if ($sensor) {

            $alertsMeasurements = [];
            foreach ($sensor->alertsMeasurements() as $key => $value) {
                $alertsMeasurements['measurement' . ($key + 1)] = $value;
            }

            $data = array_merge(
                [
                    'startTime' => $sensor->firstAlertTime(),
                    'endTime' => $sensor->lastAlertTime(),
                ],
                $alertsMeasurements
            );
            return response()->json($data);
        }
        return response()->json('No alerts found', 404);
    }

    /**
     * @param $uuid
     * @param PostMeasurementRequest $request
     * @return JsonResponse
     */
    public function createMeasurement($uuid, PostMeasurementRequest $request)
    {
        try {
            $sensor = Sensor::firstOrCreate(
                ['uuid' => $uuid],
                [
                    'uuid' => $uuid,
                    'status' => self::$STATUS_OK
                ]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $data = array_merge(['uuid' => $uuid], $request->all());
        try {
            Measurement::create($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $this->checkStatus($sensor);
        $this->alertsController->checkAlert($uuid);

        return response()->json('OK', 200);
    }

    public function checkStatus(Sensor $sensor)
    {
        if ($sensor->lastMeasurement()->co2 >= 2000) {
            try {
                $sensor->status = self::$STATUS_WARN;
                $sensor->save();
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
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
                $sensor->status = self::$STATUS_ALERT;
                $sensor->save();
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else if (count($lowMeasurements) === 3) {
            try {
                $sensor->status = self::$STATUS_OK;
                $sensor->save();
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        if ($sensor->status === self::$STATUS_ALERT) {
            Alert::create(['uuid' => $sensor->uuid, 'measurement_id' => $sensor->lastMeasurement()->id]);
        }
    }
}
