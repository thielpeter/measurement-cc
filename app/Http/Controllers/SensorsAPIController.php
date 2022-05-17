<?php

namespace App\Http\Controllers;


use App\Http\Requests\PostMeasurementRequest;
use App\Models\Alert;
use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Http\JsonResponse;

class SensorsAPIController extends Controller
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
     * @OA\Get(
     *      path="/sensors/{uuid}",
     *      operationId="getStatus",
     *      tags={"Sensors"},
     *      summary="Get status of a sensor",
     *      description="Returns status of a sensor",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="Sensor UUID",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="string"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      )
     *     )
     */
    public function getStatus($uuid)
    {
        $sensor = Sensor::where(['uuid' => $uuid])->first();
        if ($sensor) {
            return response()->json([
                'status' => $sensor->status,
            ]);
        }
        return response()->json('No sensor found', 404);
    }

    /**
     * @OA\Get(
     *      path="/sensors/{uuid}/metrics",
     *      operationId="getMetrics",
     *      tags={"Sensors"},
     *      summary="Get metrics of a sensor",
     *      description="Returns metrics of a sensor",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="Sensor UUID",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="string"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      )
     *     )
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
     * @OA\Get(
     *      path="/sensors/{uuid}/alerts",
     *      operationId="getAlerts",
     *      tags={"Sensors"},
     *      summary="Get alerts of a sensor",
     *      description="Returns alerts of a sensor",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="Sensor UUID",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="string"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      )
     *     )
     */
    public function getAlerts($uuid)
    {
        $sensor = Sensor::where(['uuid' => $uuid])->first();
        if ($sensor) {

            if(!$sensor->alerts()->count()){
                return response()->json('No alerts found', 404);
            }

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
        return response()->json('No sensor found', 404);
    }

    /**
     * @OA\Post(
     *      path="/sensors/{uuid}/measurements",
     *      operationId="postMeasurement",
     *      tags={"Sensors"},
     *      summary="Post measurement for a given sensor",
     *      description="Creates a measurement for a given sensor",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="Sensor UUID",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="co2",
     *         in="query",
     *         description="CO2 value of measurement",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="time",
     *         in="query",
     *         description="Time of measurement",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2022-05-17"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *      )
     *     )
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
//        $this->alertsController->checkAlert($uuid);

        return response()->json(['success' => 'success'], 200);
    }

    public function checkStatus(Sensor $sensor)
    {
        if ($sensor->status !== self::$STATUS_ALERT && $sensor->lastMeasurement()->co2 >= 2000) {
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
